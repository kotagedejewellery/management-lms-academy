import { readFile, writeFile } from 'node:fs/promises';

const path = new URL('../resources/js/wayfinder/index.ts', import.meta.url);
const source = await readFile(path, 'utf8');
const patched = source.replace(
    '"head" | "options";',
    '"head" | "options" | "query";',
);

if (source === patched) throw new Error('Wayfinder Method type was not found.');

await writeFile(path, patched);
