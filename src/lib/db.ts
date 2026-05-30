import { Database } from 'bun:sqlite';
import path from 'path';
import { existsSync, readdirSync } from 'fs';

const DATA_DIR = path.join(import.meta.dir, '../../data');

export function getDbPath(filename: string): string {
  const filePath = path.join(DATA_DIR, filename);
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
