const DEFAULT_MAXIMUM_DIMENSION = 1920;
const DEFAULT_WEBP_QUALITY = 0.86;

export function fitImageDimensions(width, height, maximumDimension = DEFAULT_MAXIMUM_DIMENSION) {
  const safeWidth = Math.max(1, Number(width) || 1);
  const safeHeight = Math.max(1, Number(height) || 1);
  const scale = Math.min(1, maximumDimension / Math.max(safeWidth, safeHeight));

  return {
    width: Math.max(1, Math.round(safeWidth * scale)),
    height: Math.max(1, Math.round(safeHeight * scale))
  };
}

export async function optimizeBackgroundImage(file) {
  const source = URL.createObjectURL(file);

  try {
    const image = await decodeImageSource(source);
    const dimensions = fitImageDimensions(image.naturalWidth, image.naturalHeight);
    const canvas = document.createElement("canvas");
    canvas.width = dimensions.width;
    canvas.height = dimensions.height;
    const context = canvas.getContext("2d", { alpha: true });
    if (!context) throw new Error("Canvas is unavailable.");
    context.drawImage(image, 0, 0, dimensions.width, dimensions.height);

    const optimizedBlob = await new Promise((resolve, reject) => {
      canvas.toBlob(
        (blob) => blob ? resolve(blob) : reject(new Error("Image optimization failed.")),
        "image/webp",
        DEFAULT_WEBP_QUALITY
      );
    });

    return readBlobAsDataUrl(optimizedBlob);
  } finally {
    URL.revokeObjectURL(source);
  }
}

export async function decodeImageSource(source) {
  const image = new Image();
  image.decoding = "async";
  image.src = source;
  await image.decode();

  return image;
}

function readBlobAsDataUrl(blob) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.onerror = () => reject(reader.error);
    reader.readAsDataURL(blob);
  });
}
