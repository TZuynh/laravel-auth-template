const FALLBACK_SCENES = [
  {
    id: 1,
    start: 0,
    duration: 3,
    type: 'hook',
    title: 'Hook',
    subtitle: 'Your video starts here',
    camera: 'snap_zoom',
    transition: 'impact_zoom',
    motion: {zoom_start: 1, zoom_end: 1.2, shake: 0.01, motion_blur: true},
    subtitle_cues: [],
  },
];

export const normalizeTimeline = (timeline) => {
  const fps = clampNumber(timeline.fps, 24, 60, 30);
  const width = clampNumber(timeline.width, 240, 3840, 1080);
  const height = clampNumber(timeline.height, 240, 3840, 1920);
  const scenes = Array.isArray(timeline.scenes) && timeline.scenes.length > 0 ? timeline.scenes : FALLBACK_SCENES;
  let cursor = 0;

  const normalizedScenes = scenes.map((scene, index) => {
    const duration = clampNumber(scene.duration ?? scene.duration_seconds, 1, 30, 3);
    const start = Number.isFinite(Number(scene.start)) ? Number(scene.start) : cursor;
    cursor = start + duration;

    return {
      ...scene,
      id: scene.id ?? index + 1,
      sort_order: scene.sort_order ?? index + 1,
      start,
      duration,
      title: String(scene.title || `Scene ${index + 1}`),
      subtitle: String(scene.subtitle || scene.voice_over || scene.title || ''),
      camera: String(scene.camera || 'cinematic_zoom'),
      transition: String(scene.transition || 'smooth_push'),
      type: String(scene.type || 'story'),
      motion: scene.motion && typeof scene.motion === 'object' ? scene.motion : {},
      subtitle_cues: Array.isArray(scene.subtitle_cues) ? scene.subtitle_cues : [],
    };
  });

  return {
    ...timeline,
    fps,
    width,
    height,
    duration_seconds: normalizedScenes.reduce((sum, scene) => Math.max(sum, scene.start + scene.duration), 0),
    scenes: normalizedScenes,
  };
};

export const durationInFrames = (timeline) => {
  return Math.max(1, Math.ceil((timeline.duration_seconds || 1) * (timeline.fps || 30)));
};

export const sceneFrameRange = (scene, fps) => {
  const from = Math.max(0, Math.round(scene.start * fps));
  const duration = Math.max(1, Math.round(scene.duration * fps));

  return {from, duration};
};

const clampNumber = (value, min, max, fallback) => {
  const number = Number(value);
  if (!Number.isFinite(number)) {
    return fallback;
  }

  return Math.min(max, Math.max(min, number));
};
