import React from 'react';
import {
  AbsoluteFill,
  Audio,
  Easing,
  Img,
  OffthreadVideo,
  Sequence,
  interpolate,
  useCurrentFrame,
  useVideoConfig,
} from 'remotion';
import {sceneFrameRange} from './timeline.js';

export const CinematicVideo = ({timeline}) => {
  const {fps} = useVideoConfig();

  return (
    <AbsoluteFill style={styles.root}>
      {timeline.music_url ? <Audio src={timeline.music_url} volume={0.18} /> : null}
      {timeline.voice_url ? <Audio src={timeline.voice_url} volume={1} /> : null}

      {timeline.scenes.map((scene, index) => {
        const {from, duration} = sceneFrameRange(scene, fps);

        return (
          <Sequence key={`${scene.id}-${index}`} from={from} durationInFrames={duration}>
            <Scene scene={scene} sceneIndex={index} durationInFrames={duration} />
          </Sequence>
        );
      })}

      <FilmGrain />
      <BrandPlate timeline={timeline} />
    </AbsoluteFill>
  );
};

const Scene = ({scene, sceneIndex, durationInFrames}) => {
  const frame = useCurrentFrame();
  const {width, height} = useVideoConfig();
  const progress = frame / Math.max(1, durationInFrames - 1);
  const eased = Easing.bezier(0.18, 0.74, 0.22, 1)(progress);
  const motion = scene.motion || {};
  const zoomStart = Number(motion.zoom_start || 1);
  const zoomEnd = Number(motion.zoom_end || (scene.type === 'hook' ? 1.28 : 1.12));
  const zoom = interpolate(eased, [0, 1], [zoomStart, zoomEnd], {extrapolateRight: 'clamp'});
  const shakeAmount = Number(motion.shake || 0) * Math.min(width, height);
  const shakeX = Math.sin(frame * 0.86 + sceneIndex) * shakeAmount;
  const shakeY = Math.cos(frame * 0.73 + sceneIndex) * shakeAmount;
  const panX = scene.camera === 'parallax_push' ? interpolate(eased, [0, 1], [-26, 26]) : 0;
  const panY = scene.camera === 'slow_dolly_in' ? interpolate(eased, [0, 1], [18, -18]) : 0;
  const entrance = transitionIn(scene.transition, frame, durationInFrames);
  const exit = transitionOut(scene.transition, frame, durationInFrames);
  const blur = motion.motion_blur ? interpolate(Math.abs(Math.sin(frame / 2)), [0, 1], [0, 1.6]) : 0;

  return (
    <AbsoluteFill style={{...styles.scene, opacity: Math.min(entrance.opacity, exit.opacity)}}>
      <div
        style={{
          ...styles.visualStage,
          transform: `${entrance.transform} ${exit.transform} translate(${shakeX + panX}px, ${shakeY + panY}px) scale(${zoom})`,
          filter: `blur(${blur}px) saturate(1.12) contrast(1.08)`,
        }}
      >
        <VisualAsset scene={scene} sceneIndex={sceneIndex} />
      </div>

      <LightLeak frame={frame} scene={scene} />
      <MotionOverlays scene={scene} progress={progress} frame={frame} />
      <Caption scene={scene} frame={frame} durationInFrames={durationInFrames} />
      <SceneMetadata scene={scene} />
      <TransitionFlash scene={scene} frame={frame} durationInFrames={durationInFrames} />
    </AbsoluteFill>
  );
};

const VisualAsset = ({scene, sceneIndex}) => {
  const source = scene.visual_url || '';

  if (source && source.toLowerCase().match(/\.(mp4|mov|webm)(\?|#|$)/)) {
    return <OffthreadVideo src={source} muted style={styles.mediaCover} />;
  }

  if (source) {
    return <Img src={source} style={styles.mediaCover} />;
  }

  return <GeneratedPlate scene={scene} sceneIndex={sceneIndex} />;
};

const GeneratedPlate = ({scene, sceneIndex}) => {
  const palettes = [
    ['#050816', '#1d2f55', '#d6ad62'],
    ['#08111f', '#1c5c70', '#e2e8f0'],
    ['#120716', '#54346d', '#f4c77f'],
    ['#061414', '#224d3f', '#d9f99d'],
  ];
  const palette = palettes[sceneIndex % palettes.length];

  return (
    <AbsoluteFill
      style={{
        background: `linear-gradient(145deg, ${palette[0]}, ${palette[1]} 58%, ${palette[2]})`,
      }}
    >
      <div style={{...styles.generatedGlow, background: palette[2]}} />
      <div style={styles.generatedSubject}>
        <div style={styles.generatedLens} />
        <div style={styles.generatedTitle}>{scene.visual || scene.b_roll_direction || scene.title}</div>
      </div>
      <div style={styles.generatedFloor} />
    </AbsoluteFill>
  );
};

const Caption = ({scene, frame, durationInFrames}) => {
  const words = splitWords(scene.subtitle);
  const activeIndex = activeWordIndex(scene, frame, durationInFrames, words.length);
  const isHook = scene.type === 'hook';

  return (
    <div style={isHook ? styles.hookCaptionWrap : styles.captionWrap}>
      <div style={isHook ? styles.hookCaption : styles.caption}>
        {words.map((word, index) => {
          const active = index === activeIndex;
          const emphasized = active || Boolean(scene.subtitle_cues?.[index]?.emphasis);

          return (
            <span
              key={`${word}-${index}`}
              style={{
                ...styles.captionWord,
                color: active ? '#facc15' : '#ffffff',
                transform: active ? 'translateY(-5px) scale(1.08)' : 'translateY(0) scale(1)',
                opacity: emphasized ? 1 : 0.86,
              }}
            >
              {word}
            </span>
          );
        })}
      </div>
    </div>
  );
};

const SceneMetadata = ({scene}) => {
  return (
    <div style={styles.metadata}>
      <span>{scene.shot_type || scene.type}</span>
      <span>{scene.camera}</span>
      <span>{scene.sound_effect || scene.transition}</span>
    </div>
  );
};

const MotionOverlays = ({scene, progress, frame}) => {
  const stripe = interpolate(progress, [0, 1], [-30, 130], {extrapolateRight: 'clamp'});
  const pulse = scene.type === 'hook' ? Math.abs(Math.sin(frame / 3)) : 0.25;

  return (
    <AbsoluteFill style={styles.overlayRoot}>
      <div style={{...styles.vignette, opacity: scene.type === 'cta' ? 0.58 : 0.42}} />
      <div style={{...styles.scanLine, left: `${stripe}%`, opacity: 0.12 + pulse * 0.16}} />
      {scene.asset_plan?.overlay === 'cta_button_glow' ? <div style={styles.ctaGlow} /> : null}
    </AbsoluteFill>
  );
};

const LightLeak = ({frame, scene}) => {
  const drift = Math.sin(frame / 26) * 12;
  const opacity = scene.transition === 'light_leak' || scene.type === 'emotion' ? 0.32 : 0.18;

  return <div style={{...styles.lightLeak, transform: `translateX(${drift}%) rotate(-12deg)`, opacity}} />;
};

const TransitionFlash = ({scene, frame, durationInFrames}) => {
  const startFlash = scene.transition === 'impact_zoom' ? interpolate(frame, [0, 8], [0.55, 0], {extrapolateRight: 'clamp'}) : 0;
  const endFlash = interpolate(frame, [durationInFrames - 8, durationInFrames - 1], [0, 0.38], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });

  return <AbsoluteFill style={{backgroundColor: '#ffffff', opacity: Math.max(startFlash, endFlash), mixBlendMode: 'screen'}} />;
};

const FilmGrain = () => {
  const frame = useCurrentFrame();
  const opacity = 0.025 + Math.abs(Math.sin(frame * 12.9898)) * 0.02;

  return (
    <AbsoluteFill
      style={{
        pointerEvents: 'none',
        opacity,
        backgroundImage:
          'radial-gradient(circle at 18% 22%, #fff 0, transparent 1px), radial-gradient(circle at 77% 8%, #fff 0, transparent 1px), radial-gradient(circle at 44% 80%, #fff 0, transparent 1px)',
        backgroundSize: '44px 44px',
        mixBlendMode: 'overlay',
      }}
    />
  );
};

const BrandPlate = ({timeline}) => {
  return (
    <div style={styles.brand}>
      <span>AI CINEMATIC</span>
      <strong>{timeline.aspect_ratio || '9:16'}</strong>
    </div>
  );
};

const transitionIn = (transition, frame, duration) => {
  const p = interpolate(frame, [0, Math.min(14, duration * 0.18)], [0, 1], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });

  if (transition === 'whip_pan') {
    return {opacity: p, transform: `translateX(${interpolate(p, [0, 1], [120, 0])}px)`};
  }

  if (transition === 'impact_zoom') {
    return {opacity: p, transform: `scale(${interpolate(p, [0, 1], [1.18, 1])})`};
  }

  return {opacity: p, transform: `translateY(${interpolate(p, [0, 1], [28, 0])}px)`};
};

const transitionOut = (transition, frame, duration) => {
  const p = interpolate(frame, [duration - 14, duration - 1], [0, 1], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });

  if (transition === 'clean_fade') {
    return {opacity: 1 - p * 0.88, transform: 'translateX(0)'};
  }

  if (transition === 'whip_pan') {
    return {opacity: 1, transform: `translateX(${interpolate(p, [0, 1], [0, -96])}px)`};
  }

  return {opacity: 1, transform: `scale(${interpolate(p, [0, 1], [1, 1.04])})`};
};

const activeWordIndex = (scene, frame, durationInFrames, wordCount) => {
  if (wordCount === 0) {
    return -1;
  }

  const seconds = (frame / Math.max(1, durationInFrames)) * Number(scene.duration || 1);
  const cues = Array.isArray(scene.subtitle_cues) ? scene.subtitle_cues : [];
  const cueIndex = cues.findIndex((cue) => seconds >= Number(cue.start || 0) && seconds <= Number(cue.end || 0));

  if (cueIndex >= 0) {
    return cueIndex;
  }

  return Math.min(wordCount - 1, Math.floor((frame / Math.max(1, durationInFrames)) * wordCount));
};

const splitWords = (text) => {
  return String(text || '')
    .trim()
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 22);
};

const styles = {
  root: {
    backgroundColor: '#020617',
    color: '#ffffff',
    fontFamily: 'Inter, Arial, sans-serif',
    overflow: 'hidden',
  },
  scene: {
    overflow: 'hidden',
    backgroundColor: '#020617',
  },
  visualStage: {
    position: 'absolute',
    inset: '-4%',
    transformOrigin: 'center',
  },
  mediaCover: {
    width: '100%',
    height: '100%',
    objectFit: 'cover',
  },
  overlayRoot: {
    pointerEvents: 'none',
  },
  vignette: {
    position: 'absolute',
    inset: 0,
    background: 'radial-gradient(circle at center, transparent 35%, rgba(0,0,0,.74) 100%)',
  },
  scanLine: {
    position: 'absolute',
    top: '-18%',
    width: '22%',
    height: '140%',
    background: 'linear-gradient(90deg, transparent, rgba(255,255,255,.75), transparent)',
    transform: 'rotate(12deg)',
    filter: 'blur(18px)',
  },
  lightLeak: {
    position: 'absolute',
    top: '-12%',
    right: '-16%',
    width: '50%',
    height: '78%',
    background: 'linear-gradient(90deg, rgba(250,204,21,.0), rgba(250,204,21,.52), rgba(236,72,153,.24))',
    filter: 'blur(26px)',
    mixBlendMode: 'screen',
  },
  ctaGlow: {
    position: 'absolute',
    left: '12%',
    right: '12%',
    bottom: '12%',
    height: '16%',
    borderRadius: 999,
    background: 'rgba(59,130,246,.22)',
    filter: 'blur(24px)',
  },
  captionWrap: {
    position: 'absolute',
    left: '7%',
    right: '7%',
    bottom: '9%',
    display: 'flex',
    justifyContent: 'center',
  },
  hookCaptionWrap: {
    position: 'absolute',
    left: '6%',
    right: '6%',
    bottom: '14%',
    display: 'flex',
    justifyContent: 'center',
  },
  caption: {
    display: 'flex',
    maxWidth: '92%',
    flexWrap: 'wrap',
    justifyContent: 'center',
    gap: '10px 14px',
    padding: '22px 26px',
    borderRadius: 24,
    background: 'rgba(2,6,23,.72)',
    boxShadow: '0 22px 80px rgba(0,0,0,.42)',
    fontSize: 54,
    lineHeight: 1.08,
    fontWeight: 950,
    textTransform: 'uppercase',
    textAlign: 'center',
    textShadow: '0 4px 22px rgba(0,0,0,.7)',
  },
  hookCaption: {
    display: 'flex',
    maxWidth: '96%',
    flexWrap: 'wrap',
    justifyContent: 'center',
    gap: '10px 16px',
    padding: '26px 30px',
    borderRadius: 28,
    background: 'rgba(0,0,0,.52)',
    boxShadow: '0 24px 90px rgba(0,0,0,.5)',
    fontSize: 68,
    lineHeight: 1.02,
    fontWeight: 1000,
    textTransform: 'uppercase',
    textAlign: 'center',
    textShadow: '0 6px 26px rgba(0,0,0,.85)',
  },
  captionWord: {
    display: 'inline-block',
    transition: 'none',
  },
  metadata: {
    position: 'absolute',
    left: 52,
    top: 52,
    display: 'flex',
    gap: 14,
    alignItems: 'center',
    color: '#bfdbfe',
    fontSize: 22,
    fontWeight: 900,
    letterSpacing: 3,
    textTransform: 'uppercase',
    opacity: 0.8,
  },
  brand: {
    position: 'absolute',
    right: 42,
    top: 40,
    display: 'flex',
    gap: 12,
    alignItems: 'center',
    color: '#dbeafe',
    fontSize: 18,
    fontWeight: 900,
    letterSpacing: 4,
    textTransform: 'uppercase',
    opacity: 0.64,
  },
  generatedGlow: {
    position: 'absolute',
    top: '12%',
    left: '45%',
    width: '44%',
    height: '30%',
    borderRadius: '50%',
    opacity: 0.28,
    filter: 'blur(42px)',
  },
  generatedSubject: {
    position: 'absolute',
    left: '12%',
    right: '12%',
    top: '20%',
    bottom: '18%',
    borderRadius: 44,
    background: 'linear-gradient(145deg, rgba(255,255,255,.20), rgba(255,255,255,.04))',
    border: '2px solid rgba(255,255,255,.20)',
    boxShadow: '0 50px 130px rgba(0,0,0,.38)',
    overflow: 'hidden',
  },
  generatedLens: {
    position: 'absolute',
    width: '52%',
    height: '42%',
    left: '24%',
    top: '16%',
    borderRadius: '50%',
    background: 'radial-gradient(circle, rgba(255,255,255,.75), rgba(96,165,250,.22) 48%, transparent 70%)',
    filter: 'blur(3px)',
  },
  generatedTitle: {
    position: 'absolute',
    left: '7%',
    right: '7%',
    bottom: '12%',
    color: '#ffffff',
    fontSize: 42,
    lineHeight: 1.16,
    fontWeight: 950,
    textShadow: '0 5px 24px rgba(0,0,0,.7)',
  },
  generatedFloor: {
    position: 'absolute',
    left: '-10%',
    right: '-10%',
    bottom: '-10%',
    height: '32%',
    background: 'radial-gradient(ellipse at center, rgba(255,255,255,.24), rgba(2,6,23,.0) 68%)',
  },
};
