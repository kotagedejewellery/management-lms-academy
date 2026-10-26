import { readFile, writeFile } from 'node:fs/promises';

const path = new URL('../resources/js/wayfinder/index.ts', import.meta.url);
const source = await readFile(path, 'utf8');
const unpatchedMethod = '"head" | "options";';
const patchedMethod = '"head" | "options" | "query";';
const patched = source.replace(
    unpatchedMethod,
    patchedMethod,
);

if (source === patched && !source.includes(patchedMethod)) {
    throw new Error('Wayfinder Method type was not found.');
}

if (source !== patched) {
    await writeFile(path, patched);
}
