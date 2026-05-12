<?php

namespace App\Services\Marketing;

use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use Throwable;

class EdgeTtsService
{
    private const VOICES = [
        'vi-VN-HoaiMyNeural' => 'Nữ Hoài My',
        'vi-VN-NamMinhNeural' => 'Nam Minh',
    ];

    public function synthesize(User $user, string $text, string $voice, string $tone = 'expert'): array
    {
        $text = $this->normalizeText($text);
        if ($text === '') {
            throw new RuntimeException('Nội dung đọc đang trống.');
        }

        $voice = array_key_exists($voice, self::VOICES) ? $voice : 'vi-VN-HoaiMyNeural';
        $rate = $this->rate($tone);
        $hash = sha1($user->id . '|' . $voice . '|' . $rate . '|' . $text);
        $relativePath = "content-voice/{$user->id}/{$hash}.mp3";
        $absolutePath = Storage::disk('public')->path($relativePath);

        $cached = Storage::disk('public')->exists($relativePath);
        if (!$cached) {
            File::ensureDirectoryExists(dirname($absolutePath));
            $this->runEdgeTts($voice, $rate, $text, $absolutePath);
        }

        return [
            'url' => asset('storage/' . $relativePath),
            'path' => $relativePath,
            'voice' => $voice,
            'voice_label' => self::VOICES[$voice],
            'rate' => $rate,
            'cached' => $cached,
        ];
    }

    public static function voices(): array
    {
        return self::VOICES;
    }

    private function runEdgeTts(string $voice, string $rate, string $text, string $absolutePath): void
    {
        $commands = $this->commandCandidates($voice, $rate, $text, $absolutePath);
        $lastError = null;

        foreach ($commands as $command) {
            try {
                $process = new Process($command, base_path(), $this->processEnvironment());
                $process->setTimeout(180);
                $process->run();

                if ($process->isSuccessful() && File::exists($absolutePath) && File::size($absolutePath) > 0) {
                    return;
                }

                $lastError = trim($process->getErrorOutput() ?: $process->getOutput());
            } catch (ProcessFailedException | ProcessTimedOutException | ProcessSignaledException $exception) {
                $lastError = $exception->getMessage();
            } catch (Throwable $exception) {
                $lastError = $exception->getMessage();
            }
        }

        throw new RuntimeException(
            'Edge TTS chưa chạy được. Cài bằng `pip install edge-tts` và đảm bảo lệnh `edge-tts` có trong PATH. '
            . ($lastError ? "Chi tiết: {$lastError}" : '')
        );
    }

    private function commandCandidates(string $voice, string $rate, string $text, string $absolutePath): array
    {
        $baseArgs = [
            '--voice', $voice,
            '--rate', $rate,
            '--text', $text,
            '--write-media', $absolutePath,
        ];

        $configured = trim((string) env('EDGE_TTS_BINARY', ''));
        $configuredPython = trim((string) env('EDGE_TTS_PYTHON', ''));
        $commands = [];
        if ($configured !== '') {
            $commands[] = [$configured, ...$baseArgs];
        }

        foreach ($this->pythonCandidates($configuredPython) as $python) {
            $commands[] = [$python, '-m', 'edge_tts', ...$baseArgs];
        }

        $commands[] = [$this->edgeTtsExecutableCandidate(), ...$baseArgs];
        $commands[] = ['python', '-m', 'edge_tts', ...$baseArgs];
        $commands[] = ['py', '-m', 'edge_tts', ...$baseArgs];

        return array_values(array_unique($commands, SORT_REGULAR));
    }

    private function pythonCandidates(string $configuredPython): array
    {
        $candidates = [];
        if ($configuredPython !== '') {
            $candidates[] = $configuredPython;
        }

        $localAppData = getenv('LOCALAPPDATA') ?: 'C:\Users\\' . get_current_user() . '\AppData\Local';
        $candidates[] = $localAppData . '\Python\pythoncore-3.14-64\python.exe';
        $candidates[] = $localAppData . '\Python\bin\python.exe';
        $candidates[] = 'python';
        $candidates[] = 'py';

        return array_values(array_filter(array_unique($candidates), fn (string $candidate): bool => $candidate !== ''));
    }

    private function edgeTtsExecutableCandidate(): string
    {
        $localAppData = getenv('LOCALAPPDATA') ?: 'C:\Users\\' . get_current_user() . '\AppData\Local';

        return $localAppData . '\Python\pythoncore-3.14-64\Scripts\edge-tts.exe';
    }

    private function processEnvironment(): array
    {
        $systemRoot = getenv('SystemRoot') ?: getenv('SYSTEMROOT') ?: 'C:\Windows';
        $userProfile = getenv('USERPROFILE') ?: 'C:\Users\\' . get_current_user();
        $localAppData = getenv('LOCALAPPDATA') ?: $userProfile . '\AppData\Local';
        $path = getenv('PATH') ?: getenv('Path') ?: '';
        $pythonScripts = $localAppData . '\Python\pythoncore-3.14-64\Scripts';
        $pythonBin = $localAppData . '\Python\bin';

        return [
            'SystemRoot' => $systemRoot,
            'SYSTEMROOT' => $systemRoot,
            'WINDIR' => getenv('WINDIR') ?: $systemRoot,
            'windir' => getenv('windir') ?: $systemRoot,
            'USERPROFILE' => $userProfile,
            'LOCALAPPDATA' => $localAppData,
            'APPDATA' => getenv('APPDATA') ?: $userProfile . '\AppData\Roaming',
            'TEMP' => getenv('TEMP') ?: sys_get_temp_dir(),
            'TMP' => getenv('TMP') ?: sys_get_temp_dir(),
            'PATH' => $pythonScripts . PATH_SEPARATOR . $pythonBin . PATH_SEPARATOR . $path,
            'Path' => $pythonScripts . PATH_SEPARATOR . $pythonBin . PATH_SEPARATOR . $path,
        ];
    }

    private function normalizeText(string $text): string
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/https?:\/\/\S+/u', '', $text) ?? $text;
        $text = preg_replace('/#[\pL\pN_-]+/u', '', $text) ?? $text;
        $text = preg_replace('/[\x{1F1E6}-\x{1FAFF}\x{2600}-\x{27BF}]/u', '', $text) ?? $text;
        $text = preg_replace('/[•*_~`>#|[\]{}]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return Str::limit(trim($text), 6000, '');
    }

    private function rate(string $tone): string
    {
        return match ($tone) {
            'viral', 'direct' => '+0%',
            'premium' => '-10%',
            'friendly' => '-3%',
            default => '-6%',
        };
    }
}
