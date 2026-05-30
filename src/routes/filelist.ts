import { listDbFiles } from '../lib/db';

export function handleFilelist(): Response {
  try {
    const files = listDbFiles();
    return Response.json(files);
  } catch (err: any) {
    return Response.json({ error: err.message }, { status: 500 });
  }
}
