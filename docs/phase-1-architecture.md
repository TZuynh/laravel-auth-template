# PHASE 1 - Complete Project Architecture

Project: AI cinematic ecommerce video generator SaaS  
Stack: Laravel 11, PHP 8.3, Blade, AlpineJS, TailwindCSS, Vite, MySQL, Redis, FFmpeg, Remotion, GSAP, Shotstack  
AI providers: OpenAI, Flux image generation, Runway AI video/image generation, Kling AI fallback, ElevenLabs voice

This phase defines architecture only. No migrations, models, controllers, jobs, UI implementation, FFmpeg commands, or provider integrations are implemented in this phase.

## 1. Architecture Goal

The platform must feel like a real AI cinematic video system, not a slideshow editor.

The product should generate premium short-form ecommerce videos with:

- cinematic camera movement
- parallax depth
- realistic product presentation
- dynamic lighting
- bloom, glow, grain, motion blur, depth of field
- premium transitions
- kinetic typography
- voice-over, music, subtitles
- TikTok/Reels/Shorts pacing
- export-ready MP4 files

The rendering architecture must support both AI-generated media and deterministic composition. Runway is the primary AI motion provider for cinematic text-to-video and image-to-video scene clips, Kling is the fallback motion provider, Flux/Runway generate premium visual references, and ElevenLabs generates voice-over. The rendering layer turns those assets into polished, timed, layered videos.

## 2. Core Product Modules

```text
AI Director
  Generates strategy, scripts, prompts, scenes, voice-over text, camera plans.

Product Studio
  Manages ecommerce products, product imagery, brand assets, visual references.

Scene Engine
  Converts scripts and product context into cinematic scenes.

Asset Generation
  Generates images, video shots, voice-over, subtitles, and music choices.

Render Engine
  Builds layered scenes, transitions, audio, subtitles, color grading, final MP4.

Export Manager
  Manages variants for 9:16, 16:9, 1:1 and download-ready files.

Template System
  Stores reusable cinematic structures and prompt recipes.

Usage and Billing
  Tracks provider usage, render minutes, storage, and export costs.
```

## 3. Recommended Folder Structure

```text
app/
  Domain/
    Identity/
      Models/
      Enums/
      Repositories/
      Services/

    Products/
      Actions/
      Data/
      Enums/
      Models/
      Repositories/
      Services/

    ProductAssets/
      Actions/
      Data/
      Enums/
      Models/
      Repositories/
      Services/

    VideoProjects/
      Actions/
      Data/
      Enums/
      Models/
      Repositories/
      Services/

    Scenes/
      Actions/
      Data/
      Enums/
      Models/
      Repositories/
      Services/

    Prompts/
      Actions/
      Data/
      Enums/
      Models/
      Repositories/
      Services/

    AI/
      Contracts/
      Data/
      Enums/
      Providers/
        OpenAI/
        Flux/
        Runway/
        Kling/
        ElevenLabs/
      Services/

    Rendering/
      Actions/
      Data/
      Enums/
      Jobs/
      Models/
      Pipelines/
        Steps/
      Repositories/
      Services/
      FFmpeg/
      Remotion/

    Audio/
      Actions/
      Data/
      Enums/
      Models/
      Repositories/
      Services/

    Templates/
      Actions/
      Data/
      Enums/
      Models/
      Repositories/
      Services/

    Transitions/
      Data/
      Enums/
      Models/
      Repositories/
      Services/

    Exports/
      Actions/
      Data/
      Enums/
      Models/
      Repositories/
      Services/

    Subtitles/
      Actions/
      Data/
      Enums/
      Models/
      Services/

  Http/
    Controllers/
      App/
        AiDirectorController.php
        SceneEditorController.php
        RenderHistoryController.php
        ExportManagerController.php
        TemplateManagerController.php
    Requests/
    ViewModels/

  Jobs/
    AI/
    Rendering/
    Audio/
    Exports/

  Support/
    FFmpeg/
    Remotion/
    Storage/
    Tenancy/
    Usage/
    Media/

config/
  ai.php
  runway.php
  shotstack.php
  ffmpeg.php
  remotion.php
  rendering.php
  video-presets.php
  cinematic-styles.php
  queue-groups.php

database/
  migrations/
  seeders/
  factories/

resources/
  views/
    layouts/
      app.blade.php
    components/
      app/
      ui/
      video/
      timeline/
      forms/
      dashboard/
      render/
    ai-director/
      index.blade.php
    scenes/
      editor.blade.php
    renders/
      history.blade.php
    exports/
      manager.blade.php
    templates/
      manager.blade.php

  js/
    alpine/
      ai-director.js
      scene-editor.js
      timeline.js
      render-progress.js
    motion/
      gsap-presets.js

  remotion/
    compositions/
    components/
    scenes/
    transitions/
    effects/

  css/
    app.css

routes/
  web.php
  api.php

storage/
  app/
    workspaces/{workspace_uuid}/
      products/
      projects/{project_uuid}/
        source/
        references/
        generated/
          images/
          videos/
        audio/
          voice/
          music/
        subtitles/
        clips/
        remotion/
        ffmpeg/
        exports/
        logs/
```

## 4. Domain Architecture

Use a modular monolith. Laravel remains the framework, but the business system is organized by domain.

### Identity Domain

Owns users, workspaces, roles, membership, and usage permissions.

Responsibilities:

- user ownership
- workspace separation
- access control
- usage limits
- billing hooks

### Products Domain

Owns ecommerce product records.

Responsibilities:

- product catalog data
- product metadata
- SEO/product descriptions
- product context for AI

### Product Assets Domain

Owns uploaded and generated product assets.

Responsibilities:

- product images
- gallery images
- transparent product cutouts
- reference media
- AI-generated product visuals
- asset quality status

### Video Projects Domain

Owns the high-level video project.

Responsibilities:

- selected product
- language
- style
- aspect ratio
- duration
- target platform
- project settings
- render/export state

### Scenes Domain

Owns scene structure and timeline data.

Responsibilities:

- scene titles
- scene order
- cinematic description
- camera movement
- visual prompts
- voice-over text
- subtitle text
- transition plan
- duration

### Prompts Domain

Owns prompt records and provider responses.

Responsibilities:

- base prompts
- optimized prompts
- provider-specific prompts
- negative prompts
- prompt versions
- prompt usage audit

### AI Domain

Owns provider abstraction and AI generation workflows.

Responsibilities:

- OpenAI script/prompt generation
- Flux image generation
- Runway text-to-video and image-to-video generation
- Kling video generation as fallback
- ElevenLabs voice
- provider switching
- fallback logic
- rate limiting
- usage tracking
- provider task polling and output persistence

### Rendering Domain

Owns Remotion and FFmpeg orchestration.

Responsibilities:

- scene composition planning
- layered video rendering
- FFmpeg filtering
- transitions
- subtitles
- audio mixing
- final exports
- render job progress

### Audio Domain

Owns voice profiles and music tracks.

Responsibilities:

- TTS voice profiles
- generated voice-over files
- music selection
- volume normalization
- voice/music mix planning

### Templates Domain

Owns reusable cinematic AI templates.

Responsibilities:

- Runway-style prompt recipes
- Kling-style motion recipes
- TikTok ad templates
- Apple-style product templates
- scene structures
- default transitions and camera moves

### Exports Domain

Owns final video variants.

Responsibilities:

- 9:16 TikTok
- 16:9 YouTube
- 1:1 Instagram
- export file metadata
- download URLs
- final status

## 5. Service Layer

Services should represent business capabilities, not framework plumbing.

```text
ProductContextService
  Builds AI-ready product context from product, assets, brand, metadata.

ProductAssetService
  Resolves source image, gallery, cutouts, generated visuals, and references.

AiDirectorService
  Creates marketing concept, script, hooks, CTA, and cinematic structure.

MarketingScriptService
  Generates short-form viral scripts in Vietnamese or English.

SceneGenerationService
  Splits script into 4 cinematic scenes:
  1. Hook opening
  2. Product reveal
  3. Feature transformation
  4. CTA ending

CinematicPromptService
  Generates image prompts, video prompts, camera movements, lighting notes,
  negative prompts, and provider-specific prompt variants.

PromptOptimizationService
  Improves prompts for OpenAI, Flux, Runway, Kling, and cinematic motion language.

AiProviderManager
  Selects provider, fallback provider, tracks usage, handles rate limits.

VisualGenerationService
  Generates cinematic product images and video shots through AI providers.

RunwayVideoGenerationService
  Creates Runway text-to-video and image-to-video tasks, stores provider task IDs,
  polls render status, downloads output URLs into owned storage, and normalizes
  generated clips into scene_assets.

VoiceGenerationService
  Generates voice-over audio with ElevenLabs or fallback providers.

MusicSelectionService
  Selects music by video style, mood, pacing, BPM, and target platform.

SubtitleService
  Generates subtitle text, word timing, SRT, ASS, and kinetic typography data.

RenderPlanningService
  Converts scenes and assets into render instructions.

RemotionCompositionService
  Builds high-level animation composition with GSAP-style timing concepts.

FFmpegRenderService
  Executes deterministic FFmpeg render stages.

RenderProgressService
  Updates progress, current step, ETA, and errors.

ExportService
  Creates final MP4 records, variants, metadata, and download URLs.
```

## 6. Repository Layer

Repositories isolate query logic and persistence concerns.

```text
UserRepository
  findWorkspaceUsers()
  findRenderableOwner()

ProductRepository
  searchSelectable()
  findWithAssets()
  findForAiContext()

ProductAssetRepository
  listForProduct()
  findPrimaryImage()
  findBestCutout()
  createGeneratedAsset()

VideoProjectRepository
  createDraft()
  findEditable()
  listDashboardProjects()
  updateStatus()
  updateSettings()

VideoSceneRepository
  replaceScenes()
  listOrdered()
  updatePrompts()
  updateTiming()
  attachSceneAsset()
  markGenerated()

AiPromptRepository
  createPromptVersion()
  listForProject()
  markProviderResponse()
  trackUsage()

RenderJobRepository
  createQueued()
  markRunning()
  updateProgress()
  markFailed()
  markCompleted()
  findRecoverableFailures()

ExportRepository
  createPending()
  markReady()
  listForProject()

SubtitleRepository
  createForScene()
  listForProject()

VoiceProfileRepository
  listActiveByLanguage()
  findDefault()

MusicTrackRepository
  listByMood()
  findLicensed()

AiTemplateRepository
  listActive()
  findByStyle()
  findSystemTemplate()

TransitionRepository
  listByStyle()
  findDefaultForTemplate()

SceneAssetRepository
  createGenerated()
  listByScene()
  markReady()
```

## 7. Queue Architecture

Use Redis queues with Laravel Horizon.

Separate queues by workload type:

```text
ai-text
  OpenAI script, prompt optimization, scene generation.

ai-image
  Flux or Runway image generation, product visual generation, and reference image creation.

ai-video
  Runway Gen motion generation, Kling fallback generation, provider task polling, and output download.

ai-provider-polling
  Long-running external task polling, provider status sync, retry handoff, and output URL capture.

ai-voice
  ElevenLabs TTS generation.

render-remotion
  Remotion animation previews and frame sequences.

render-ffmpeg
  FFmpeg clip generation, transitions, subtitles, audio mix, final MP4.

exports
  final export variants and storage transfer.

maintenance
  cleanup, stale job recovery, usage aggregation.
```

Recommended worker split:

```text
web node
  handles HTTP only

ai worker
  high network concurrency
  lower CPU usage

render worker
  FFmpeg installed
  high CPU/RAM
  local fast disk

export worker
  object storage upload
  metadata extraction
```

Render job chain:

```text
CreateRenderJob
  -> BuildProductContextJob
  -> GenerateMarketingScriptJob
  -> GenerateScenePlanJob
  -> GenerateAiPromptsJob
  -> GenerateSceneImagesJob
  -> CreateRunwaySceneVideoTasksJob
  -> PollRunwaySceneVideoTasksJob
  -> DownloadRunwaySceneAssetsJob
  -> GenerateFallbackSceneVideosJob
  -> GenerateVoiceOverJob
  -> BuildSubtitlesJob
  -> RenderSceneCompositionsJob
  -> ComposeSceneClipsWithFFmpegJob
  -> AddTransitionsJob
  -> MixVoiceAndMusicJob
  -> ApplyCinematicGradeJob
  -> ExportFinalMp4Job
  -> CreateExportRecordJob
  -> CleanupTempFilesJob
```

Progress weights:

```text
product_context: 3%
script_generation: 8%
scene_generation: 10%
prompt_generation: 8%
image_generation: 16%
video_generation: 18%
voice_generation: 8%
subtitle_generation: 4%
scene_rendering: 12%
transitions_audio_grade: 8%
final_export: 5%
```

## 8. AI Generation Pipeline

The AI pipeline is not a one-shot prompt. It is staged and auditable.

```text
1. Product context
   - product name
   - features
   - price/category
   - brand tone
   - product images
   - ecommerce metadata

2. Marketing strategy
   - target platform
   - video style
   - audience
   - emotional trigger
   - luxury/viral/cinematic tone

3. Script generation
   - strong hook
   - emotional trigger
   - product transformation
   - CTA

4. Scene generation
   - 4 scenes
   - title
   - cinematic description
   - duration
   - camera movement
   - transition
   - animation style

5. Prompt generation
   - image prompt
   - video prompt
   - negative prompt
   - provider-specific prompt

6. Asset generation
   - Flux or Runway image generation for references and story frames
   - Runway image-to-video or text-to-video generation for primary scene clips
   - Kling video generation as fallback provider
   - ElevenLabs voice

7. Render plan
   - layered composition
   - motion path
   - subtitles
   - voice/music timing

8. Final render
   - Remotion/GSAP-style animation
   - FFmpeg composition
   - MP4 export
```

Prompt style requirements:

```text
luxury cinematic product commercial
dark gallery lighting
ultra realistic product showcase
depth of field
cinematic neon reflections
premium social ad
high-end ecommerce commercial
realistic product texture
soft rim light
premium studio lighting
dynamic camera movement
```

## 8.1. Runway AI Video Provider Architecture

Runway should be treated as the primary AI motion layer, not the final compositor. It generates cinematic scene clips from prompts and product references. Laravel stores every prompt, task ID, provider response, generated URL, and downloaded asset path. FFmpeg, Remotion, or Shotstack then assemble the final ad with subtitles, CTA text, brand marks, voice, music, and color grade.

Recommended model routing:

```text
Gen-4.5 text-to-video
  Use the image_to_video endpoint without promptImage for original cinematic shots
  when no exact product reference is required.

Gen-4.5 or Gen-4 Turbo image-to-video
  Use for ecommerce product videos where product identity must stay stable.

Gen-4 Image / image generation
  Use for product hero frames, storyboards, character references, and visual concepts.

Gen-4 Aleph video-to-video
  Use later for restyling or enhancing existing product footage.
```

Runway prompt contract:

```text
subject
  exact product name, character, gender, clothing, hand/person context

product_identity
  product source image or reference image, label preservation requirements

action
  what physically changes during the shot

camera
  dolly in, orbit, macro rack focus, handheld micro movement, parallax push

lighting
  dark gallery lighting, soft rim light, neon reflections, premium studio light

environment
  luxury shelf, modern kitchen, gallery, ecommerce studio, lifestyle scene

mood
  cinematic, luxury, emotional, viral TikTok, Apple commercial

composition
  hero product center, negative space for typography, no AI text overlays

format
  aspect ratio, duration, platform pacing
```

Runway generation workflow:

```text
1. Select product and source product image.
2. Generate or select a story frame with product, character, gender, and scene context.
3. Create provider-specific Runway prompt for each scene.
4. Submit text-to-video or image-to-video task.
5. Store provider_task_id, model, payload, prompt, and request hash.
6. Poll provider task status from queue.
7. Download generated output immediately into project storage.
8. Create scene_assets rows for every downloaded clip.
9. Normalize clip duration, frame rate, resolution, and audio tracks.
10. Send normalized clips into FFmpeg/Remotion/Shotstack final composition.
```

Runway client configuration:

```text
config/runway.php
  base_url: https://api.dev.runwayml.com
  api_key: env('RUNWAY_API_KEY')
  api_version: env('RUNWAY_API_VERSION', '2024-11-06')
  timeout_seconds: 120
  retry_attempts: 3
  retry_backoff_ms: 1000, 3000, 7000
```

Every Runway HTTP request must send the API key and the `X-Runway-Version` header. Keep the version pinned in config so provider behavior does not silently change.

Runway request contract:

```text
Endpoint
  POST /v1/image_to_video

Required headers
  Authorization: Bearer RUNWAY_API_KEY
  Content-Type: application/json
  X-Runway-Version: 2024-11-06

Image-to-video payload
  model: gen4.5
  promptImage: public image URL or data URI
  promptText: cinematic motion prompt
  ratio: 720:1280, 1280:720, 960:960, or another supported model dimension
  duration: 5 or 8

Text-to-video payload
  same payload, but omit promptImage
```

Aspect ratio mapping:

```text
9:16 TikTok/Reels
  Prefer provider dimensions such as 720:1280 or 832:1104, then final export 1080x1920.

16:9 YouTube
  Prefer provider dimensions such as 1280:720, then final export 1920x1080.

1:1 Instagram
  Prefer provider square output such as 960:960 when available.
```

Runway queue jobs:

```text
CreateRunwayTaskJob
  Validates payload and creates text-to-video, image-to-video, or image task.

PollRunwayTaskJob
  Polls status with exponential backoff and updates ai_prompts/render_jobs.

DownloadRunwayAssetJob
  Downloads temporary provider output URLs into owned storage before expiry.

NormalizeSceneClipJob
  Uses FFmpeg to normalize codec, fps, aspect ratio, duration, and silent audio track.
```

Professional render workflow based on Runway:

```text
1. Build product reference frame
   Use the selected product image, character, gender, wardrobe, product interaction,
   and one clear visual objective.

2. Generate or enhance a story frame
   Prefer Runway Gen-4 image or Flux for a clean hero image before motion.

3. Generate motion plate
   Use image-to-video for product-accurate shots; use text-to-video only for abstract
   establishing shots or background plates.

4. Validate motion plate
   Run FFprobe, duration check, resolution check, black-frame check, and readable-frame
   thumbnail extraction before the clip enters final composition.

5. Final compositor owns the ad
   Render subtitles, CTA, logo, price, audio, music, color grade, film grain, bloom,
   and final transitions in FFmpeg, Remotion, or Shotstack.

6. Export variants
   Produce social-ready MP4 outputs for 9:16, 16:9, and 1:1 from the same scene plan.
```

Retry and idempotency rules:

- retry HTTP 429 and 5xx with exponential backoff and jitter
- do not blindly resubmit a task if a provider_task_id already exists
- polling retries are safe because they read external task state
- output URLs must be copied to local/object storage because provider asset URLs are temporary
- prompt payloads should be hashed so duplicate scene requests can be detected
- provider failures should fall back from Runway to Kling only after the Runway task is clearly failed or expired
- production jobs must tolerate 429 rate limits, 503 outages, input validation errors, and throttled task states

Cinematic quality rules:

- prefer image-to-video for ecommerce because product identity is more stable than text-to-video
- keep generated clips short, usually 5 or 8 seconds, then create pacing in the final compositor
- render readable text, CTA, logo, price, and subtitles with Remotion/FFmpeg/Shotstack instead of asking the AI model to generate typography
- use Runway clips as motion plates and add deterministic brand polish in the final render
- generate voice-over and music as separate audio assets; do not depend on Runway clips to contain final ad audio
- run FFprobe and black-frame validation after every downloaded clip, then regenerate or fall back if duration, dimensions, or visible frames are invalid
- for people/character ads, include character, gender, wardrobe, expression, and product interaction in the story frame and image-to-video prompt
- always generate a fallback still/image-based animated scene if video generation fails
- store every prompt and output for later quality review and prompt tuning

## 9. Scene Generation Engine Architecture

Default scene structure:

```text
Scene 1 - Hook Opening
  Purpose: stop scroll in first 1-2 seconds
  Motion: dolly in, handheld micro movement, kinetic type
  Transition out: flash bloom, whip blur, match cut

Scene 2 - Product Reveal
  Purpose: reveal product with premium lighting
  Motion: orbit, parallax, slow push
  Transition out: liquid light sweep, zoom blur

Scene 3 - Feature Transformation
  Purpose: show benefit or transformation
  Motion: macro close-up, rack focus, depth pass
  Transition out: speed ramp, bloom wipe

Scene 4 - CTA Ending
  Purpose: convert viewer
  Motion: hero lockup, soft camera settle
  Transition out: brand end frame
```

Each scene stores:

- scene title
- cinematic description
- AI prompt
- image prompt
- video prompt
- negative prompt
- camera movement
- voice-over text
- duration
- transition type
- animation style
- subtitle text
- generated assets
- render status

## 10. Cinematic Camera System

Camera movements are data, not hardcoded effects.

Camera movement enum:

```text
dolly_in
dolly_out
orbit_left
orbit_right
handheld_micro
cinematic_zoom
parallax_push
macro_rack_focus
top_down_reveal
hero_lockup
```

Camera plan fields:

```text
movement
start_scale
end_scale
start_x
end_x
start_y
end_y
rotation
depth_layers
motion_blur
focus_subject
```

The render engine uses this camera plan to create dynamic motion in Remotion and FFmpeg.

## 11. Rendering Pipeline

The system should use a hybrid renderer:

- Remotion for high-level animation composition and dynamic typography.
- GSAP concepts for easing and motion design presets.
- FFmpeg for deterministic media processing, transitions, subtitles, audio, final MP4.

Pipeline:

```text
1. Generate AI prompts
2. Generate cinematic reference images and story frames
3. Generate Runway/Kling cinematic motion clips
4. Download provider outputs into owned storage
5. Normalize all source assets
6. Build layered scene composition
7. Add camera movement and parallax
8. Add kinetic typography
9. Add cinematic transitions
10. Add subtitles
11. Add AI voice-over
12. Add music
13. Apply cinematic color grade
14. Add film grain, bloom, glow, depth effects
15. Merge all scenes
16. Export MP4
```

Render layers per scene:

```text
background_layer
  generated cinematic image/video

depth_layer
  blurred duplicate, light rays, glow, depth-of-field mask

product_layer
  product cutout or generated product hero visual

character_layer
  optional AI character/person shot

typography_layer
  kinetic hook, subtitle, CTA

lighting_layer
  bloom, glow, lens flare, gradient sweep

grain_layer
  film grain and vignette
```

## 12. FFmpeg Architecture

FFmpeg must be wrapped by structured builders. Never build shell commands from raw user input.

```text
Rendering/FFmpeg/
  FFmpegCommandBuilder
  FFmpegFilterGraphBuilder
  FFmpegProbeService
  FFmpegProgressParser
  FFmpegPathGuard
  VideoFormatResolver
  AudioMixPlanner
  SubtitleRenderer
  TransitionRenderer
  ColorGradeRenderer
```

FFmpeg responsibilities:

- scene stitching
- transition effects
- subtitle rendering
- voice/music mixing
- cinematic zoom
- blur effects
- overlay system
- color grading
- output normalization
- progress tracking
- retry-safe intermediate files

FFmpeg filter concepts:

```text
scale/crop/pad
zoompan
xfade
overlay
drawtext or ASS subtitles
gblur
unsharp
eq
curves
vignette
noise
tblend
afade
amix
loudnorm
```

Intermediate storage:

```text
projects/{project_uuid}/
  generated/images/
  generated/videos/
  audio/voice/
  audio/music/
  remotion/frames/
  clips/
    scene-001.mp4
    scene-002.mp4
  subtitles/
    scene-001.ass
    final.srt
  ffmpeg/
    filtergraph.txt
    progress.log
  exports/
    final-9x16.mp4
    final-16x9.mp4
    final-1x1.mp4
```

## 13. Remotion and GSAP Motion Architecture

Remotion should render high-fidelity animated compositions when FFmpeg filters are not expressive enough.

Use Remotion for:

- cinematic typography
- Apple-style product motion
- precise timeline composition
- scene-level parallax
- animated UI overlays
- light sweeps
- product lockups

Use GSAP concepts for:

- easing curves
- staggered text reveals
- timeline thinking
- camera movement presets

Architecture:

```text
resources/remotion/
  compositions/
    ProductVideoComposition.tsx
  scenes/
    HookOpeningScene.tsx
    ProductRevealScene.tsx
    FeatureTransformationScene.tsx
    CtaEndingScene.tsx
  components/
    ProductHero.tsx
    KineticText.tsx
    LightSweep.tsx
    FilmGrain.tsx
    DepthLayer.tsx
  transitions/
    BloomFlash.tsx
    WhipBlur.tsx
    LightSweepCut.tsx
  effects/
    ColorGrade.tsx
    MotionBlur.tsx
```

Laravel sends a render manifest JSON to Remotion. Remotion renders clips or frames. FFmpeg finalizes and exports MP4.

## 14. Database Schema Design

Architecture-level schema only.

### users

Purpose: SaaS users and creators.

Fields:

- id
- name
- email
- password
- avatar_path
- role
- status
- last_login_at
- timestamps

### products

Purpose: ecommerce products.

Fields:

- id
- user_id or workspace_id
- name
- slug
- sku
- description
- short_description
- category
- brand
- price
- currency
- metadata_json
- status
- timestamps

### product_assets

Purpose: source and generated product media.

Fields:

- id
- product_id
- type: image, gallery, cutout, reference, generated
- path
- thumbnail_path
- mime_type
- width
- height
- duration
- provider
- metadata_json
- is_primary
- status
- timestamps

### video_projects

Purpose: top-level video project.

Fields:

- id
- uuid
- user_id or workspace_id
- product_id
- ai_template_id
- title
- language
- tone
- style
- aspect_ratio
- duration_seconds
- ai_model
- prompt
- optimized_prompt
- settings_json
- status
- timestamps

### video_scenes

Purpose: ordered cinematic scenes.

Fields:

- id
- video_project_id
- sort_order
- title
- cinematic_description
- voice_over_text
- subtitle_text
- duration_seconds
- camera_movement
- transition_id
- animation_style
- status
- metadata_json
- timestamps

### ai_prompts

Purpose: prompt versions and provider responses.

Fields:

- id
- video_project_id
- video_scene_id nullable
- type: script, image, video, voice, optimization
- provider
- model
- provider_task_id nullable
- provider_status nullable
- prompt
- negative_prompt
- request_json
- response_json
- output_url nullable
- output_url_expires_at nullable
- tokens_used
- cost
- status
- timestamps

### render_jobs

Purpose: queue rendering state.

Fields:

- id
- uuid
- video_project_id
- export_id nullable
- status
- queue_name
- current_step
- progress
- attempts
- max_attempts
- error_message
- log_path
- started_at
- completed_at
- failed_at
- timestamps

### exports

Purpose: final rendered files.

Fields:

- id
- uuid
- video_project_id
- render_job_id
- aspect_ratio
- format
- resolution_width
- resolution_height
- duration_seconds
- file_path
- file_size
- checksum
- status
- metadata_json
- timestamps

### subtitles

Purpose: subtitle and kinetic text data.

Fields:

- id
- video_project_id
- video_scene_id nullable
- language
- format: srt, ass, json
- content
- timing_json
- style_json
- file_path
- timestamps

### voice_profiles

Purpose: reusable voices.

Fields:

- id
- provider
- provider_voice_id
- name
- gender
- language
- tone
- sample_path
- settings_json
- is_active
- timestamps

### music_tracks

Purpose: licensed or generated music.

Fields:

- id
- title
- mood
- bpm
- duration_seconds
- file_path
- license_type
- default_volume
- metadata_json
- is_active
- timestamps

### ai_templates

Purpose: reusable cinematic prompt and scene templates.

Fields:

- id
- name
- slug
- language
- tone
- style
- platform
- system_prompt
- script_prompt_template
- image_prompt_template
- video_prompt_template
- voice_prompt_template
- default_scene_structure_json
- default_settings_json
- is_active
- timestamps

### transitions

Purpose: reusable transition definitions.

Fields:

- id
- name
- slug
- type
- duration_seconds
- ffmpeg_filter
- remotion_component
- settings_json
- is_active
- timestamps

### scene_assets

Purpose: generated scene-specific media.

Fields:

- id
- video_scene_id
- product_asset_id nullable
- type: image, video, voice, music, overlay, depth_map
- provider
- prompt_id nullable
- provider_task_id nullable
- source_prompt_id nullable
- external_url nullable
- external_url_expires_at nullable
- path
- thumbnail_path
- mime_type
- width
- height
- duration_seconds
- status
- metadata_json
- timestamps

## 15. Reusable UI Component Architecture

The UI must look like a premium AI video platform, not an admin table app.

Blade components:

```text
components/ui/
  glass-panel.blade.php
  glow-card.blade.php
  gradient-button.blade.php
  icon-button.blade.php
  premium-select.blade.php
  prompt-textarea.blade.php
  status-pill.blade.php
  progress-ring.blade.php

components/video/
  cinematic-preview-player.blade.php
  aspect-ratio-switcher.blade.php
  render-progress.blade.php
  export-card.blade.php

components/timeline/
  scene-timeline.blade.php
  scene-card.blade.php
  transition-chip.blade.php
  audio-waveform.blade.php

components/dashboard/
  product-selector.blade.php
  template-selector.blade.php
  ai-director-panel.blade.php
  scene-generator-panel.blade.php
  render-history-panel.blade.php
```

Main page sections:

```text
1. Product selector
2. Video template selector
3. AI Director panel
4. Scene generator panel
5. Cinematic preview player
6. Generate progress system
7. Scene timeline
8. Render history
9. Export manager
```

UI style rules:

- dark luxury SaaS
- glassmorphism panels
- purple and dark blue gradients
- cinematic glow effects
- soft shadows
- responsive layouts
- hover animations
- timeline-first scene editing
- preview player as the visual center
- minimal table usage

AlpineJS responsibilities:

- prompt panel interactions
- scene timeline reorder preview
- render progress polling
- provider/model selector state
- aspect ratio preview switching
- collapsible advanced controls
- animated hover and active states

## 16. SaaS Scalability Structure

Multi-tenant concepts:

- every product/project/render/export belongs to a user or workspace
- future workspace_id should be supported even if MVP starts user-owned
- usage should be tracked by workspace
- provider costs should be auditable per render

Scalability decisions:

- web requests never perform AI generation or rendering
- Redis queues isolate heavy work
- render workers run separately from web workers
- generated assets are stored in object storage when possible
- local fast disk is used only for temporary render work
- FFmpeg jobs are idempotent and retry-safe
- provider responses are stored for debugging and cost analysis
- Runway and Kling task IDs are stored before polling to prevent duplicate paid generations
- provider output URLs are downloaded into owned storage immediately because external generated asset URLs are temporary
- provider polling runs on a separate queue so long AI tasks do not block render workers
- rate limits, usage, and cost are tracked per workspace, provider, model, and render job

Storage strategy:

```text
local temp disk
  active FFmpeg and Remotion work

object storage
  product assets
  generated AI assets
  downloaded Runway/Kling output clips
  final exports

database
  metadata, statuses, paths, usage, settings
```

Security decisions:

- validate all uploads
- restrict FFmpeg input to project storage paths
- never pass raw user text as shell arguments
- build FFmpeg commands from escaped structured arrays
- enforce tenant ownership on every query
- signed URLs for exports
- cleanup temporary files after render

## 17. Architecture Decisions

### Why modular monolith?

Laravel is strongest as a modular monolith for this stage. It avoids microservice complexity while keeping domains clean enough to split later.

### Why Redis queues?

AI generation and rendering are slow, failure-prone, and expensive. Redis queues allow retries, progress tracking, backpressure, and dedicated worker pools.

### Why hybrid Remotion plus FFmpeg?

FFmpeg is excellent for media processing and final export, but cinematic motion design and kinetic typography are easier to model in Remotion. Remotion creates expressive animated scenes. FFmpeg normalizes, stitches, mixes, grades, and exports.

### Why store prompts?

Prompt history is required for debugging, usage tracking, provider fallback, and improving cinematic quality over time.

### Why scene-first architecture?

Cinematic videos are sequences of intentional shots, not static templates. A scene-first model allows camera movement, timing, transitions, voice-over, subtitles, and AI visuals to be managed independently.

### Why assets are separate from scenes?

Products may have many source assets, while scenes may generate many derived assets. Keeping `product_assets` and `scene_assets` separate avoids mixing source media with generated render media.

## 18. Phase Boundary

This file completes architecture only.

Do not implement code until the next confirmed phase.

Next phase:

PHASE 2 - Database

Expected deliverables:

- migrations
- models
- relationships
- enums
- seeders
