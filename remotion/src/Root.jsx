import React from 'react';
import {Composition, getInputProps} from 'remotion';
import {CinematicVideo} from './CinematicVideo.jsx';
import {durationInFrames, normalizeTimeline} from './timeline.js';

const defaultTimeline = normalizeTimeline(getInputProps().timeline || {});

export const RemotionRoot = () => {
  return (
    <Composition
      id="CinematicVideo"
      component={CinematicVideo}
      durationInFrames={durationInFrames(defaultTimeline)}
      fps={defaultTimeline.fps}
      width={defaultTimeline.width}
      height={defaultTimeline.height}
      defaultProps={{
        timeline: defaultTimeline,
      }}
      calculateMetadata={({props}) => {
        const timeline = normalizeTimeline(props.timeline || {});

        return {
          durationInFrames: durationInFrames(timeline),
          fps: timeline.fps,
          width: timeline.width,
          height: timeline.height,
          props: {
            timeline,
          },
        };
      }}
    />
  );
};
