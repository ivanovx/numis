import { useState, useEffect, useCallback, useRef } from "react";
import { createRoot } from "react-dom/client";
import CoinCard from "./components/CoinCard";
import CoinDetailModal from "./components/CoinDetailModal";
import type { Coin, Filters } from "./lib/types";

function App() {
  const [dbFiles, setDbFiles] = useState<string[]>([]);
  const [selectedFile, setSelectedFile] = useState<string>("");
  const [coins, setCoins] = useState<Coin[]>([]);
  const [filters, setFilters] = useState<Filters>({
    status: [],
    country: [],
    year: [],
    series: [],
    type: [],
    period: [],
    mint: [],
  });
  const [loading, setLoading] = useState(false);
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("");
  const [countryFilter, setCountryFilter] = useState("");
  const [yearFilter, setYearFilter] = useState("");
  const [selectedCoinId, setSelectedCoinId] = useState<number | null>(null);
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const searchTimeout = useRef<any>(null);

  useEffect(() => {
    fetch("/api/filelist")
      .then((r) => r.json())
      .then((files: string[]) => {
        setDbFiles(files);
        if (files.length > 0) setSelectedFile(files[0]);
      })
      .catch(console.error);
  }, []);

  useEffect(() => {
    if (!selectedFile) return;
    fetch(`/api/filters?f=${encodeURIComponent(selectedFile)}`)
      .then((r) => r.json())
      .then(setFilters)
      .catch(console.error);
  }, [selectedFile]);

  const loadCoins = useCallback(() => {
    if (!selectedFile) return;
    setLoading(true);
    const params = new URLSearchParams({ f: selectedFile });
    if (search) params.set("search", search);
    if (statusFilter) params.set("status_filter", statusFilter);
    if (countryFilter) params.set("country_filter", countryFilter);
    if (yearFilter) params.set("year_filter", yearFilter);

    fetch(`/api/coins?${params}`)
      .then((r) => r.json())
      .then((data) => {
        setCoins(Array.isArray(data) ? data : []);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, [selectedFile, search, statusFilter, countryFilter, yearFilter]);

  useEffect(() => {
    if (searchTimeout.current) clearTimeout(searchTimeout.current);
    searchTimeout.current = setTimeout(loadCoins, 300);
    return () => clearTimeout(searchTimeout.current);
  }, [loadCoins]);

  const clearFilters = () => {
    setSearch("");
    setStatusFilter("");
    setCountryFilter("");
    setYearFilter("");
  };
  const hasActiveFilters =
    search || statusFilter || countryFilter || yearFilter;

  const groupedCoins = coins.reduce((acc: Record<string, Coin[]>, coin) => {
    const yr = coin.year
      ? String(coin.year < 0 ? `${Math.abs(coin.year)} BC` : coin.year)
      : "Unknown";
    if (!acc[yr]) acc[yr] = [];
    acc[yr].push(coin);
    return acc;
  }, {});

  return (
    <div className="min-h-screen bg-[#0a0a14] text-white">
      <header className="sticky top-0 z-30 bg-[#0a0a14]/90 backdrop-blur-xl border-b border-white/5">
        <div className="max-w-screen-xl mx-auto px-4 sm:px-6 h-16 flex items-center gap-4">
          <div className="flex items-center gap-3 flex-shrink-0">
            <div className="w-8 h-8 rounded-full bg-gradient-to-br from-amber-300 to-amber-600 flex items-center justify-center shadow-[0_0_15px_rgba(251,191,36,0.3)]">
              <svg
                className="w-4 h-4 text-black"
                viewBox="0 0 24 24"
                fill="currentColor"
              >
                <circle cx="12" cy="12" r="8" />
                <circle
                  cx="12"
                  cy="12"
                  r="4"
                  fill="none"
                  stroke="rgba(0,0,0,0.3)"
                  strokeWidth="1.5"
                />
              </svg>
            </div>
            <span className="font-semibold text-white/90 hidden sm:block tracking-tight">
              Numis
            </span>
          </div>

          {dbFiles.length > 1 && (
            <select
              value={selectedFile}
              onChange={(e) => setSelectedFile(e.target.value)}
              className="bg-white/5 border border-white/10 text-white/80 text-sm rounded-lg px-3 py-1.5 focus:outline-none focus:border-amber-400/50 max-w-[180px]"
            >
              {dbFiles.map((f) => (
                <option key={f} value={f} className="bg-[#1a1a2e]">
                  {f.replace(".db", "")}
                </option>
              ))}
            </select>
          )}
          {dbFiles.length === 1 && (
            <span className="text-white/40 text-sm hidden sm:block">
              {selectedFile.replace(".db", "")}
            </span>
          )}

          <div className="flex-1 max-w-md relative">
            <svg
              className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
            >
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.35-4.35" />
            </svg>
            <input
              type="text"
              placeholder="Search coins..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="w-full bg-white/5 border border-white/10 text-white placeholder-slate-500 text-sm rounded-xl pl-9 pr-4 py-2 focus:outline-none focus:border-amber-400/40"
            />
          </div>

          <button
            onClick={() => setSidebarOpen(!sidebarOpen)}
            className={`flex items-center gap-2 px-3 py-2 rounded-xl text-sm transition-all border ${hasActiveFilters ? "bg-amber-400/20 text-amber-300 border-amber-400/30" : "bg-white/5 text-slate-400 border-white/10 hover:text-white"}`}
          >
            <svg
              className="w-4 h-4"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
            >
              <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
            </svg>
            <span className="hidden sm:inline">Filter</span>
            {hasActiveFilters && (
              <span className="w-1.5 h-1.5 bg-amber-400 rounded-full" />
            )}
          </button>

          <div className="text-slate-500 text-sm flex-shrink-0 hidden md:block">
            {loading ? "..." : `${coins.length} coins`}
          </div>
        </div>
      </header>

      <div className="max-w-screen-xl mx-auto px-4 sm:px-6 py-6 flex gap-6">
        {sidebarOpen && (
          <aside className="w-56 flex-shrink-0 space-y-4">
            <div className="flex items-center justify-between">
              <h3 className="text-xs font-semibold text-slate-400 uppercase tracking-widest">
                Filters
              </h3>
              {hasActiveFilters && (
                <button
                  onClick={clearFilters}
                  className="text-xs text-amber-400 hover:text-amber-300"
                >
                  Clear all
                </button>
              )}
            </div>
            {filters.status.length > 0 && (
              <div className="space-y-1">
                <label className="text-[10px] text-slate-500 uppercase tracking-widest block">
                  Status
                </label>
                <select
                  value={statusFilter}
                  onChange={(e) => setStatusFilter(e.target.value)}
                  className="w-full bg-white/5 border border-white/10 text-white/80 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-amber-400/40"
                >
                  <option value="" className="bg-[#1a1a2e]">
                    All
                  </option>
                  {filters.status.filter(Boolean).map((s) => (
                    <option key={s} value={s} className="bg-[#1a1a2e]">
                      {s}
                    </option>
                  ))}
                </select>
              </div>
            )}
            {filters.country.length > 0 && (
              <div className="space-y-1">
                <label className="text-[10px] text-slate-500 uppercase tracking-widest block">
                  Country
                </label>
                <select
                  value={countryFilter}
                  onChange={(e) => setCountryFilter(e.target.value)}
                  className="w-full bg-white/5 border border-white/10 text-white/80 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-amber-400/40"
                >
                  <option value="" className="bg-[#1a1a2e]">
                    All
                  </option>
                  {filters.country.filter(Boolean).map((c) => (
                    <option key={c} value={c} className="bg-[#1a1a2e]">
                      {c}
                    </option>
                  ))}
                </select>
              </div>
            )}
            {filters.year.length > 0 && (
              <div className="space-y-1">
                <label className="text-[10px] text-slate-500 uppercase tracking-widest block">
                  Year
                </label>
                <select
                  value={yearFilter}
                  onChange={(e) => setYearFilter(e.target.value)}
                  className="w-full bg-white/5 border border-white/10 text-white/80 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-amber-400/40"
                >
                  <option value="" className="bg-[#1a1a2e]">
                    All
                  </option>
                  {filters.year.filter(Boolean).map((y) => (
                    <option key={y} value={y} className="bg-[#1a1a2e]">
                      {y}
                    </option>
                  ))}
                </select>
              </div>
            )}
          </aside>
        )}

        <main className="flex-1 min-w-0">
          {!selectedFile ? (
            <div className="flex flex-col items-center justify-center py-32 gap-4">
              <div className="w-20 h-20 rounded-full bg-white/5 border border-white/10 flex items-center justify-center">
                <svg
                  className="w-10 h-10 text-slate-600"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="1"
                >
                  <circle cx="12" cy="12" r="9" />
                </svg>
              </div>
              <div className="text-center">
                <p className="text-white/60 font-medium">No database found</p>
                <p className="text-slate-500 text-sm mt-1">
                  Place your{" "}
                  <code className="text-amber-400 bg-amber-400/10 px-1 rounded">
                    .db
                  </code>{" "}
                  files in the{" "}
                  <code className="text-amber-400 bg-amber-400/10 px-1 rounded">
                    data/
                  </code>{" "}
                  directory
                </p>
              </div>
            </div>
          ) : loading ? (
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
              {Array.from({ length: 15 }).map((_, i) => (
                <div
                  key={i}
                  className="bg-white/5 rounded-2xl overflow-hidden animate-pulse"
                >
                  <div className="aspect-square bg-white/5" />
                  <div className="p-3 space-y-2">
                    <div className="h-3 bg-white/5 rounded w-3/4" />
                    <div className="h-3 bg-white/5 rounded w-1/2" />
                  </div>
                </div>
              ))}
            </div>
          ) : coins.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-32 gap-4">
              <p className="text-white/40">No coins match your search</p>
              {hasActiveFilters && (
                <button
                  onClick={clearFilters}
                  className="text-amber-400 hover:text-amber-300 text-sm underline"
                >
                  Clear filters
                </button>
              )}
            </div>
          ) : (
            <div className="space-y-10">
              {Object.entries(groupedCoins).map(([year, yearCoins]) => (
                <section key={year}>
                  <div className="flex items-center gap-3 mb-4">
                    <h2 className="text-amber-400/80 font-mono text-sm font-semibold tracking-wider">
                      {year}
                    </h2>
                    <div className="flex-1 h-px bg-white/5" />
                    <span className="text-slate-600 text-xs font-mono">
                      {yearCoins.length}
                    </span>
                  </div>
                  <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                    {yearCoins.map((coin) => (
                      <CoinCard
                        key={coin.id}
                        coin={coin}
                        onClick={() => setSelectedCoinId(coin.id)}
                      />
                    ))}
                  </div>
                </section>
              ))}
            </div>
          )}
        </main>
      </div>

      {selectedCoinId !== null && (
        <CoinDetailModal
          coinId={selectedCoinId}
          dbFile={selectedFile}
          onClose={() => setSelectedCoinId(null)}
        />
      )}
    </div>
  );
}

const root = createRoot(document.getElementById("root")!);
root.render(<App />);
