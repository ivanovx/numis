import { Database } from 'bun:sqlite';
import { existsSync, readdirSync } from 'fs';
import { join } from 'path';

const DATA_DIR = join(import.meta.dir, '..', 'data');

export function getDbPath(filename: string): string {
  const filePath = join(DATA_DIR, filename);
  if (!existsSync(filePath)) {
    throw new Error(`Database file not found: ${filePath}`);
  }
  return filePath;
}

export function openDb(dbPath: string): Database {
  return new Database(dbPath, { readonly: true });
}

export function listDbFiles(): string[] {
  if (!existsSync(DATA_DIR)) return [];
  return readdirSync(DATA_DIR)
    .filter(f => f.endsWith('.db'))
    .sort((a, b) => a.toLowerCase().localeCompare(b.toLowerCase()));
}

/** Convert raw BLOB column to base64 string, or pass through strings */
export function blobToBase64(val: unknown): string | null {
  if (val instanceof Uint8Array || Buffer.isBuffer(val)) {
    return Buffer.from(val as Uint8Array).toString('base64');
  }
  if (typeof val === 'string') return val;
  return null;
}
