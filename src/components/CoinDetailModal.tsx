import { useEffect, useState } from "react";

interface CoinDetailModalProps {
  coinId: number;
  dbFile: string;
  onClose: () => void;
}

const STATUS_LABELS: Record<string, string> = {
  owned: "✓ Owned",
  wish: "♡ Wish List",
  sold: "↗ Sold",
  ordered: "⟳ Ordered",
  bidding: "⚡ Bidding",
  missing: "? Missing",
  duplicate: "⊕ Duplicate",
};

const STATUS_COLORS: Record<string, string> = {
  owned: "text-emerald-400",
  wish: "text-amber-400",
  sold: "text-rose-400",
  ordered: "text-blue-400",
  bidding: "text-violet-400",
  missing: "text-slate-400",
  duplicate: "text-orange-400",
};

export default function CoinDetailModal({
  coinId,
  dbFile,
  onClose,
}: CoinDetailModalProps) {
  const [coin, setCoin] = useState<Record<string, any> | null>(null);
  const [loading, setLoading] = useState(true);
  const [activeImg, setActiveImg] = useState<"obverse" | "reverse">("obverse");

  useEffect(() => {
    setLoading(true);
    fetch(`/api/coin?f=${encodeURIComponent(dbFile)}&id=${coinId}`)
      .then((r) => r.json())
      .then((data) => {
        setCoin(data);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, [coinId, dbFile]);

  useEffect(() => {
    const handleKey = (e: KeyboardEvent) => {
      if (e.key === "Escape") onClose();
    };
    window.addEventListener("keydown", handleKey);
    return () => window.removeEventListener("keydown", handleKey);
  }, [onClose]);

  const fields = coin
    ? [
        { label: "Country", value: coin.country },
        { label: "Region", value: coin.region },
        { label: "Period", value: coin.period },
        { label: "Ruler", value: coin.ruler },
        {
          label: "Year",
          value: coin.year
            ? coin.year < 0
              ? `${Math.abs(coin.year)} BC`
              : coin.year
            : null,
        },
        { label: "Issue Date", value: coin.issuedate },
        {
          label: "Value",
          value:
            coin.value && coin.unit ? `${coin.value} ${coin.unit}` : coin.value,
        },
        { label: "Type", value: coin.type },
        { label: "Series", value: coin.series },
        { label: "Material", value: coin.material },
        { label: "Mint", value: coin.mint },
        { label: "Mintmark", value: coin.mintmark },
        { label: "Mintage", value: coin.mintage },
        { label: "Grade", value: coin.grade },
        { label: "Condition", value: coin.condition },
        { label: "Storage", value: coin.storage },
        { label: "Quantity", value: coin.quantity },
        { label: "Purchase Price", value: coin.payprice },
        { label: "Purchase Date", value: coin.paydate },
      ].filter(
        (f) => f.value !== null && f.value !== undefined && f.value !== "",
      )
    : [];

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center p-4"
      onClick={(e) => {
        if (e.target === e.currentTarget) onClose();
      }}
    >
      <div
        className="absolute inset-0 bg-black/80 backdrop-blur-md"
        onClick={onClose}
      />
      <div className="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto bg-[#12121f] border border-white/10 rounded-3xl shadow-2xl">
        <div className="sticky top-0 z-10 bg-[#12121f]/95 backdrop-blur-sm border-b border-white/5 px-6 py-4 flex items-start justify-between">
          <div>
            {loading ? (
              <div className="h-5 w-48 bg-white/10 rounded animate-pulse" />
            ) : (
              <>
                <h2 className="text-white font-semibold text-lg leading-tight">
                  {coin?.title}
                </h2>
                {coin?.status && (
                  <span
                    className={`text-sm mt-1 block ${STATUS_COLORS[coin.status] || "text-slate-400"}`}
                  >
                    {STATUS_LABELS[coin.status] || coin.status}
                  </span>
                )}
              </>
            )}
          </div>
          <button
            onClick={onClose}
            className="text-slate-500 hover:text-white transition-colors ml-4 flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10"
          >
            <svg
              className="w-5 h-5"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
            >
              <path d="M18 6L6 18M6 6l12 12" />
            </svg>
          </button>
        </div>

        {loading ? (
          <div className="p-8 flex items-center justify-center">
            <div className="w-8 h-8 border-2 border-amber-400/30 border-t-amber-400 rounded-full animate-spin" />
          </div>
        ) : coin && !coin.error ? (
          <div className="p-6 space-y-6">
            {(coin.obverseImg || coin.reverseImg) && (
              <div className="space-y-3">
                <div className="aspect-square rounded-2xl overflow-hidden bg-[#0d0d1a] max-w-xs mx-auto">
                  {activeImg === "obverse" && coin.obverseImg ? (
                    <img
                      src={`data:image/jpeg;base64,${coin.obverseImg}`}
                      alt="Obverse"
                      className="w-full h-full object-contain"
                    />
                  ) : activeImg === "reverse" && coin.reverseImg ? (
                    <img
                      src={`data:image/jpeg;base64,${coin.reverseImg}`}
                      alt="Reverse"
                      className="w-full h-full object-contain"
                    />
                  ) : (
                    <div className="w-full h-full flex items-center justify-center text-slate-600 text-sm">
                      No image
                    </div>
                  )}
                </div>
                {coin.obverseImg && coin.reverseImg && (
                  <div className="flex justify-center gap-2">
                    <button
                      onClick={() => setActiveImg("obverse")}
                      className={`px-4 py-1.5 rounded-full text-sm transition-all ${activeImg === "obverse" ? "bg-amber-400 text-black font-semibold" : "text-slate-400 hover:text-white border border-white/10"}`}
                    >
                      Obverse
                    </button>
                    <button
                      onClick={() => setActiveImg("reverse")}
                      className={`px-4 py-1.5 rounded-full text-sm transition-all ${activeImg === "reverse" ? "bg-amber-400 text-black font-semibold" : "text-slate-400 hover:text-white border border-white/10"}`}
                    >
                      Reverse
                    </button>
                  </div>
                )}
              </div>
            )}
            {coin.subject && (
              <div className="bg-white/5 rounded-xl p-4">
                <p className="text-slate-400 text-xs uppercase tracking-widest mb-1">
                  Description
                </p>
                <p className="text-white/80 text-sm leading-relaxed">
                  {coin.subject}
                </p>
              </div>
            )}
            {coin.features && (
              <div className="bg-white/5 rounded-xl p-4">
                <p className="text-slate-400 text-xs uppercase tracking-widest mb-1">
                  Features
                </p>
                <p className="text-white/80 text-sm leading-relaxed">
                  {coin.features}
                </p>
              </div>
            )}
            {fields.length > 0 && (
              <div className="grid grid-cols-2 gap-2">
                {fields.map(({ label, value }) => (
                  <div key={label} className="bg-white/5 rounded-xl p-3">
                    <p className="text-slate-500 text-[10px] uppercase tracking-widest mb-0.5">
                      {label}
                    </p>
                    <p className="text-white/90 text-sm font-medium">
                      {String(value)}
                    </p>
                  </div>
                ))}
              </div>
            )}
          </div>
        ) : (
          <div className="p-8 text-center text-slate-500">
            Could not load coin details.
          </div>
        )}
      </div>
    </div>
  );
}
