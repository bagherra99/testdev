const shimmerItems = Array.from({ length: 4 });

function Results({ items, loading, total, hasSearched }) {
  // Etat vide (avant recherche ou aucun résultat)
  if (!loading && !items.length) {
    if (!hasSearched) {
      return (
        <section className="mt-12 text-center text-slate-500">
          <p>Commencez par saisir un mot-clé et une localisation pour lancer votre recherche.</p>
        </section>
      );
    }

    return (
      <section className="mt-12 text-center text-slate-600">
        <p className="text-lg font-medium">Aucun commerce trouvé pour ces critères.</p>
        <p className="mt-1 text-sm text-slate-500">Essayez une autre ville ou un autre mot-clé.</p>
      </section>
    );
  }

  return (
    <section className="mt-12">
      {/* En-tête récapitulatif */}
      <div className="flex items-center justify-between text-slate-600">
        <div>
          <p className="text-sm uppercase tracking-[0.3em] text-sky-500">Résultats</p>
          <p className="text-2xl font-semibold text-slate-900">{loading ? 'Exploration...' : `${total} commerces`}</p>
        </div>
        {hasSearched && (
          <p className="text-sm text-slate-500">{loading ? 'Chargement' : 'Données en temps réel Geoapify'}</p>
        )}
      </div>

      <div className="mt-6 grid gap-5 md:grid-cols-2">
        {loading
          ? shimmerItems.map((_, index) => (
              <article
                key={`shimmer-${index}`}
                className="glass rounded-3xl p-5 animate-pulse"
              >
                <div className="flex gap-4">
                  <div className="h-20 w-20 rounded-2xl bg-slate-200/70" />
                  <div className="flex-1 space-y-3">
                    <div className="h-4 w-3/4 rounded bg-slate-200/70" />
                    <div className="h-4 w-1/2 rounded bg-slate-200/70" />
                    <div className="h-4 w-2/3 rounded bg-slate-200/70" />
                  </div>
                </div>
              </article>
            ))
          : items.map((business) => (
              <article
                key={business.id}
                className="glass rounded-3xl p-5 border border-transparent transition hover:-translate-y-1 hover:border-sky-200 hover:shadow-card"
              >
                <div className="flex gap-4">
                  {business.photo ? (
                    <img
                      src={business.photo}
                      alt={business.name}
                      loading="lazy"
                      className="h-20 w-20 rounded-2xl object-cover"
                    />
                  ) : (
                    <div className="h-20 w-20 rounded-2xl bg-gradient-to-br from-sky-400 via-blue-400 to-indigo-400 flex items-center justify-center text-2xl font-bold text-white">
                      {business.name.charAt(0)}
                    </div>
                  )}

                  <div className="flex-1">
                    <div className="flex items-start justify-between gap-3">
                      <div>
                        <h3 className="text-lg font-semibold text-slate-900">{business.name}</h3>
                        <p className="text-sm text-slate-500">{business.locality}</p>
                      </div>
                      {business.website && (
                        <a
                          href={business.website}
                          target="_blank"
                          rel="noreferrer"
                          className="inline-flex items-center gap-1 rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-sky-600 transition hover:border-sky-400"
                        >
                          Visiter
                          <span aria-hidden="true">↗</span>
                        </a>
                      )}
                    </div>

                    <p className="mt-3 text-sm text-slate-600">{business.address}</p>

                    <div className="mt-4 flex flex-wrap gap-3 text-sm text-slate-500">
                      {business.phone && (
                        <span className="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-slate-700">
                          📞 {business.phone}
                        </span>
                      )}
                      {business.latitude && business.longitude && (
                        <span className="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-slate-700">
                          📍 {business.latitude.toFixed(3)}, {business.longitude.toFixed(3)}
                        </span>
                      )}
                    </div>
                  </div>
                </div>
              </article>
            ))}
      </div>
    </section>
  );
}

export default Results;

