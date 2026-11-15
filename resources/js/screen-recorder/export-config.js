const PACKET_COUNT_SAFETY_FACTOR = 4 / 3;
const PACKET_COUNT_PADDING = 64;
const MAXIMUM_AUDIO_PACKET_DURATION = 0.01;

export function createReservedMp4TrackMetadata(duration, frameRate = 30) {
  const safeDuration = Math.max(0, Number(duration) || 0);
  const safeFrameRate = Math.max(1, Math.min(60, Number(frameRate) || 30));

  return {
    video: {
      maximumPacketCount: Math.max(
        1,
        Math.ceil(safeDuration * safeFrameRate * PACKET_COUNT_SAFETY_FACTOR) + PACKET_COUNT_PADDING
      )
    },
    audio: {
      maximumPacketCount: Math.max(
        1,
        Math.ceil(safeDuration / MAXIMUM_AUDIO_PACKET_DURATION * PACKET_COUNT_SAFETY_FACTOR) + PACKET_COUNT_PADDING
      )
    }
  };
}
