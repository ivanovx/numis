import { NextResponse } from 'next/server';
import { listDbFiles } from '@/lib/db';

export async function GET() {
  try {
    const files = listDbFiles();
    return NextResponse.json(files);
  } catch (err: any) {
    return NextResponse.json({ error: err.message }, { status: 500 });
  }
}
