function SearchForm({ keyword, location, onKeywordChange, onLocationChange, onSubmit, loading }) {
  return (
    <form className="grid gap-4 md:grid-cols-3" onSubmit={onSubmit}>
      <label className="flex flex-col gap-2 text-slate-700">
        <span className="text-sm font-semibold uppercase tracking-wider text-slate-500">Mot-clé</span>
        <div className="relative">
          <input
            type="text"
            value={keyword}
            onChange={(event) => onKeywordChange(event.target.value)}
            placeholder="restaurants, coiffeurs..."
            className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-900 placeholder-slate-400 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-200 transition"
          />
          <span className="pointer-events-none absolute inset-y-0 right-4 flex items-center text-sky-400">⌕</span>
        </div>
      </label>

      <label className="flex flex-col gap-2 text-slate-700">
        <span className="text-sm font-semibold uppercase tracking-wider text-slate-500">
          Ville ou code postal
        </span>
        <div className="relative">
          <input
            type="text"
            value={location}
            onChange={(event) => onLocationChange(event.target.value)}
            placeholder="Paris, 69000..."
            className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-900 placeholder-slate-400 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-200 transition"
          />
          <span className="pointer-events-none absolute inset-y-0 right-4 flex items-center text-sky-400">📍</span>
        </div>
      </label>

      <div className="flex flex-col justify-end">
        <button
          type="submit"
          disabled={loading}
          className="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-sky-400 via-sky-500 to-blue-500 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-sky-200 transition hover:scale-[1.02] disabled:cursor-not-allowed disabled:opacity-60"
        >
          {loading ? (
            <>
              <span className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white/60 border-t-transparent" />
              Recherche en cours
            </>
          ) : (
            'Lancer la recherche'
          )}
        </button>
      </div>
    </form>
  );
}

export default SearchForm;

