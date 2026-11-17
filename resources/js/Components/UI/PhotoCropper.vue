<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { ZoomIn, ZoomOut } from 'lucide-vue-next';

/**
 * Frame a profile photo: drag to move, slider / wheel / pinch to zoom. Emits a square JPEG blob,
 * so whatever the original size, only a small file is uploaded.
 */
const props = defineProps({
  src: { type: String, required: true }, // object URL of the chosen file
  output: { type: Number, default: 512 },
});
const emit = defineEmits(['done', 'cancel']);

const VIEW = 288; // px, the square frame on screen
const img = ref(null);
const natural = ref({ w: 0, h: 0 });
const zoom = ref(1); // 1 = image just covers the frame
const offset = ref({ x: 0, y: 0 }); // image centre relative to frame centre, in screen px

const baseScale = computed(() => (natural.value.w ? VIEW / Math.min(natural.value.w, natural.value.h) : 1));
const scale = computed(() => baseScale.value * zoom.value);
const drawn = computed(() => ({ w: natural.value.w * scale.value, h: natural.value.h * scale.value }));

// Keep the frame covered: the image can't be dragged past its edges.
const clamp = (o) => {
  const maxX = Math.max(0, (drawn.value.w - VIEW) / 2);
  const maxY = Math.max(0, (drawn.value.h - VIEW) / 2);
  return { x: Math.min(maxX, Math.max(-maxX, o.x)), y: Math.min(maxY, Math.max(-maxY, o.y)) };
};

const imgStyle = computed(() => ({
  width: `${drawn.value.w}px`,
  height: `${drawn.value.h}px`,
  transform: `translate(${(VIEW - drawn.value.w) / 2 + offset.value.x}px, ${(VIEW - drawn.value.h) / 2 + offset.value.y}px)`,
}));

const onLoad = () => {
  natural.value = { w: img.value.naturalWidth, h: img.value.naturalHeight };
};

const setZoom = (z) => {
  zoom.value = Math.min(4, Math.max(1, z));
  offset.value = clamp(offset.value);
};

// Drag (mouse + touch via pointer events); two fingers pinch-zoom.
const pointers = new Map();
let start = null;
const onPointerDown = (e) => {
  e.currentTarget.setPointerCapture(e.pointerId);
  pointers.set(e.pointerId, { x: e.clientX, y: e.clientY });
  start = { offset: { ...offset.value }, zoom: zoom.value, pts: [...pointers.values()].map((p) => ({ ...p })) };
};
const onPointerMove = (e) => {
  if (!pointers.has(e.pointerId) || !start) return;
  pointers.set(e.pointerId, { x: e.clientX, y: e.clientY });
  const pts = [...pointers.values()];
  if (pts.length >= 2 && start.pts.length >= 2) {
    const d = (a, b) => Math.hypot(a.x - b.x, a.y - b.y);
    setZoom(start.zoom * (d(pts[0], pts[1]) / d(start.pts[0], start.pts[1])));
  } else if (pts.length === 1 && start.pts.length === 1) {
    offset.value = clamp({ x: start.offset.x + pts[0].x - start.pts[0].x, y: start.offset.y + pts[0].y - start.pts[0].y });
  }
};
const onPointerUp = (e) => {
  pointers.delete(e.pointerId);
  start = pointers.size ? { offset: { ...offset.value }, zoom: zoom.value, pts: [...pointers.values()].map((p) => ({ ...p })) } : null;
};
const onWheel = (e) => setZoom(zoom.value * (e.deltaY < 0 ? 1.08 : 1 / 1.08));

// Arrow keys move, +/- zoom (keyboard users).
const onKey = (e) => {
  if (e.key === 'Escape') return emit('cancel');
  const step = 10;
  const moves = { ArrowLeft: [step, 0], ArrowRight: [-step, 0], ArrowUp: [0, step], ArrowDown: [0, -step] };
  if (moves[e.key]) { e.preventDefault(); offset.value = clamp({ x: offset.value.x + moves[e.key][0], y: offset.value.y + moves[e.key][1] }); }
  if (e.key === '+' || e.key === '=') setZoom(zoom.value * 1.1);
  if (e.key === '-') setZoom(zoom.value / 1.1);
};
onMounted(() => document.addEventListener('keydown', onKey));
onUnmounted(() => document.removeEventListener('keydown', onKey));

const busy = ref(false);
const save = () => {
  busy.value = true;
  // The frame's top-left corner, in the original image's pixels.
  const sx = (drawn.value.w / 2 - VIEW / 2 - offset.value.x) / scale.value;
  const sy = (drawn.value.h / 2 - VIEW / 2 - offset.value.y) / scale.value;
  const side = VIEW / scale.value;
  const size = Math.min(props.output, Math.round(side)); // don't upscale small photos

  const canvas = document.createElement('canvas');
  canvas.width = size;
  canvas.height = size;
  const ctx = canvas.getContext('2d');
  ctx.fillStyle = '#fff'; // transparent PNGs get a white background
  ctx.fillRect(0, 0, size, size);
  ctx.imageSmoothingQuality = 'high';
  ctx.drawImage(img.value, sx, sy, side, side, 0, 0, size, size);
  canvas.toBlob((blob) => { busy.value = false; if (blob) emit('done', blob); }, 'image/jpeg', 0.9);
};
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click="emit('cancel')">
    <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl" role="dialog" aria-modal="true" aria-label="Position your photo" @click.stop>
      <h3 class="text-base font-semibold text-gray-900">Position your photo</h3>
      <p class="mt-1 text-sm text-gray-500">Drag to move, zoom to fit.</p>

      <div class="mx-auto mt-4 select-none overflow-hidden rounded-xl bg-white ring-1 ring-gray-200"
        :style="{ width: `${VIEW}px`, height: `${VIEW}px`, touchAction: 'none' }"
        tabindex="0" aria-label="Photo. Drag or use the arrow keys to move it."
        @pointerdown="onPointerDown" @pointermove="onPointerMove" @pointerup="onPointerUp" @pointercancel="onPointerUp" @wheel.prevent="onWheel">
        <div class="relative h-full w-full cursor-grab active:cursor-grabbing">
          <img ref="img" :src="src" alt="" draggable="false" @load="onLoad"
            class="pointer-events-none absolute left-0 top-0 max-w-none" :style="imgStyle" />
          <!-- Round preview: darken outside the circle -->
          <div class="pointer-events-none absolute inset-0 rounded-full shadow-[0_0_0_9999px_rgba(255,255,255,0.7)] ring-2 ring-indigo-500/70"></div>
        </div>
      </div>

      <div class="mt-4 flex items-center gap-3">
        <button type="button" class="rounded p-1 text-gray-500 hover:bg-gray-100" aria-label="Zoom out" @click="setZoom(zoom / 1.2)"><ZoomOut class="h-4 w-4" /></button>
        <input type="range" min="1" max="4" step="0.01" :value="zoom" @input="setZoom(+$event.target.value)" aria-label="Zoom"
          class="h-1.5 flex-1 cursor-pointer appearance-none rounded-full bg-gray-200 accent-indigo-600" />
        <button type="button" class="rounded p-1 text-gray-500 hover:bg-gray-100" aria-label="Zoom in" @click="setZoom(zoom * 1.2)"><ZoomIn class="h-4 w-4" /></button>
      </div>

      <div class="mt-6 flex justify-end gap-2">
        <button type="button" class="btn-secondary" @click="emit('cancel')">Cancel</button>
        <button type="button" class="btn-primary" :disabled="!natural.w || busy" @click="save">Use photo</button>
      </div>
    </div>
  </div>
</template>
