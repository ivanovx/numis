# Coin Catalog

A Next.js application for browsing your coin collection from a SQLite database. Built with Tailwind CSS, featuring chronological grouping, denomination sorting, filtering, and detailed coin views with images.

## Setup

### 1. Install dependencies
```bash
npm install
```

### 2. Add your database
Place your `.db` collection file(s) in the `data/` directory at the project root:
```
coin-catalog/
  data/
    my-collection.db     <- your SQLite file here
  app/
  components/
  ...
```

The app expects the OpenNumismat database schema with tables: `coins`, `images`, `photos`.

### 3. Run the development server
```bash
npm run dev
```

Open http://localhost:3000

### 4. Build for production
```bash
npm run build && npm start
```

## Features

- **Chronological grouping** — coins grouped by year (oldest first), sorted by denomination within each year
- **Live search** — filter by title as you type
- **Filter panel** — filter by status, country, year
- **Detail modal** — full coin details with obverse/reverse photos
- **Multi-collection** — dropdown if multiple `.db` files are present
- **Status badges** — color-coded owned / wish / sold / bidding etc.
