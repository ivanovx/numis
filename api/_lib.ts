import { Database } from "bun:sqlite";

export const MAX_PREVIEW_IMAGE_HEIGHT = 54 * 4;
export const API_KEY_NAME = "access_token";
export const API_KEY = process.env.API_KEY ?? "";

// Path to the .db file — set DB_FILE in your Vercel env vars
// e.g. DB_FILE=/var/task/data/collection.db
const DB_FILE = process.env.DB_FILE;
if (!DB_FILE) throw new Error("DB_FILE environment variable is not set");

// ── Response helpers ────────────────────────────────────────────────────────

export function json(data: unknown, status = 200): Response {
  return new Response(JSON.stringify(data), {
    status,
    headers: { "Content-Type": "application/json" },
  });
}

export function forbidden(): Response {
  return json({ detail: "Could not validate API Key" }, 403);
}

// ── Auth ────────────────────────────────────────────────────────────────────

export function requireApiKey(req: Request): boolean {
  return req.headers.get(API_KEY_NAME) === API_KEY;
}

// ── Database ────────────────────────────────────────────────────────────────

export function getDb(): Database {
  return new Database(DB_FILE!, { readonly: true });
}

// ── Guard: run handler only if API key is valid ─────────────────────────────

export function withAuth(
  req: Request,
  handler: () => Response | Promise<Response>
): Response | Promise<Response> {
  if (!requireApiKey(req)) return forbidden();
  return handler();
}
