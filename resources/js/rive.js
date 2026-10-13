import { Rive, RuntimeLoader } from '@rive-app/canvas-lite';
import wasmUrl from '@rive-app/canvas-lite/rive.wasm?url';

// Serve the Rive runtime's WebAssembly from our own build instead of the unpkg CDN default
RuntimeLoader.setWasmUrl(wasmUrl);

export { Rive };
