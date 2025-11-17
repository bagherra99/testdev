function Pagination({ page, pages, onChange }) {
  if (pages <= 1) {
    return null;
  }

  // Génère les boutons numérotés selon le nombre total de pages
  const buttons = [];
  for (let i = 1; i <= pages; i += 1) {
    buttons.push(
      <button
        key={i}
        type="button"
        onClick={() => onChange(i)}
        className={`h-10 w-10 rounded-full text-sm font-semibold transition ${
          i === page ? 'bg-white text-slate-900 shadow-lg' : 'text-slate-600 hover:bg-slate-200'
        }`}
      >
        {i}
      </button>,
    );
  }

  return (
    <div className="mt-10 flex flex-wrap items-center justify-center gap-3 text-slate-700">
      <button
        type="button"
        onClick={() => onChange(page - 1)}
        disabled={page === 1}
        className="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 disabled:cursor-not-allowed disabled:border-slate-100 disabled:text-slate-400"
      >
        ← Précédent
      </button>

      <div className="flex flex-wrap gap-2">{buttons}</div>

      <button
        type="button"
        onClick={() => onChange(page + 1)}
        disabled={page === pages}
        className="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 disabled:cursor-not-allowed disabled:border-slate-100 disabled:text-slate-400"
      >
        Suivant →
      </button>
    </div>
  );
}

export default Pagination;

