import { Coin } from '../lib/types';

interface CoinCardProps {
  coin: Coin;
  onClick: () => void;
}

const STATUS_COLORS: Record<string, string> = {
  owned: 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
  wish: 'bg-amber-500/20 text-amber-300 border-amber-500/30',
  sold: 'bg-rose-500/20 text-rose-300 border-rose-500/30',
  ordered: 'bg-blue-500/20 text-blue-300 border-blue-500/30',
  bidding: 'bg-violet-500/20 text-violet-300 border-violet-500/30',
  missing: 'bg-slate-500/20 text-slate-300 border-slate-500/30',
  duplicate: 'bg-orange-500/20 text-orange-300 border-orange-500/30',
};

export default function CoinCard({ coin, onClick }: CoinCardProps) {
  const statusClass = STATUS_COLORS[coin.status] || 'bg-slate-500/20 text-slate-300 border-slate-500/30';

  return (
    <button
      onClick={onClick}
      className="group relative w-full text-left bg-[#1a1a2e] border border-white/5 rounded-2xl overflow-hidden hover:border-amber-400/40 hover:shadow-[0_0_30px_rgba(251,191,36,0.08)] transition-all duration-300 hover:-translate-y-0.5"
    >
      <div className="relative aspect-square bg-gradient-to-br from-[#0d0d1a] to-[#1a1a2e] flex items-center justify-center overflow-hidden">
        {coin.image ? (
          <img
            src={`data:image/jpeg;base64,${coin.image}`}
            alt={coin.title}
            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
          />
        ) : (
          <div className="flex flex-col items-center gap-2 opacity-30">
            <svg className="w-12 h-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1">
              <circle cx="12" cy="12" r="9" />
              <path d="M12 6v2m0 8v2M8 12h2m4 0h2" />
            </svg>
            <span className="text-xs font-mono text-slate-500">NO IMAGE</span>
          </div>
        )}
        {coin.year && (
          <div className="absolute top-2 left-2 bg-black/60 backdrop-blur-sm text-amber-300 text-xs font-mono px-2 py-0.5 rounded-full border border-amber-400/20">
            {coin.year < 0 ? `${Math.abs(coin.year)} BC` : coin.year}
          </div>
        )}
      </div>
      <div className="p-3 space-y-2">
        <p className="text-white/90 text-sm font-medium leading-tight line-clamp-2 group-hover:text-amber-100 transition-colors">
          {coin.title}
        </p>
        <div className="flex items-center justify-between gap-2">
          {(coin.value || coin.unit) && (
            <span className="text-amber-400/80 text-xs font-mono">
              {coin.value} {coin.unit}
            </span>
          )}
          {coin.status && (
            <span className={`text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full border ${statusClass}`}>
              {coin.status}
            </span>
          )}
        </div>
        {coin.country && (
          <p className="text-slate-500 text-xs truncate">{coin.country}</p>
        )}
      </div>
    </button>
  );
}
