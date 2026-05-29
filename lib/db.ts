import { createClient } from '@libsql/client';
import path from 'path';
import fs from 'fs';

export function getDbPath(filename: string): string {
  const dataDir = path.join(process.cwd(), 'data');
  const filePath = path.join(dataDir, filename);
  if (!fs.existsSync(filePath)) {
    throw new Error(`Database file not found: ${filePath}`);
  }
  return filePath;
}

export function openDb(dbPath: string) {
  return createClient({ url: `file:${dbPath}` });
}

export function listDbFiles(): string[] {
  const dataDir = path.join(process.cwd(), 'data');
  if (!fs.existsSync(dataDir)) return [];
  return fs.readdirSync(dataDir)
    .filter(f => f.endsWith('.db'))
    .sort((a, b) => a.toLowerCase().localeCompare(b.toLowerCase()));
}
