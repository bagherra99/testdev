import { useMemo, useState } from 'react';
import SearchForm from './components/SearchForm.jsx';
import Results from './components/Results.jsx';
import Pagination from './components/Pagination.jsx';

const DEFAULT_API_URL = 'http://localhost:8000/api/search.php';

function App() {
  const [keyword, setKeyword] = useState('restaurants');
  const [location, setLocation] = useState('Paris');
  const [results, setResults] = useState([]);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalResults, setTotalResults] = useState(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [hasSearched, setHasSearched] = useState(false);
  const [lastQuery, setLastQuery] = useState({ keyword: 'restaurants', location: 'Paris' });

  const apiUrl = useMemo(() => import.meta.env.VITE_API_BASE_URL ?? DEFAULT_API_URL, []);

  // Récupère les données depuis l’API en conservant les derniers critères soumis
  const fetchResults = async (targetPage = 1, formOverride) => {
    const formKeyword = formOverride?.keyword ?? keyword;
    const formLocation = formOverride?.location ?? location;

    if (!formKeyword.trim() || !formLocation.trim()) {
      setError('Merci de remplir les deux champs.');
      return;
    }

    setLoading(true);
    setError('');

    const params = new URLSearchParams({
      keyword: formKeyword.trim(),
      location: formLocation.trim(),
      page: String(targetPage),
    });

    try {
      const response = await fetch(`${apiUrl}?${params.toString()}`);
      if (!response.ok) {
        const payload = await response.json().catch(() => ({}));
        throw new Error(payload.error ?? 'Erreur serveur');
      }

      const payload = await response.json();
      const fetchedResults = payload.results ?? [];
      setResults(fetchedResults);
      setPage(payload.pagination?.page ?? targetPage);
      setTotalPages(payload.pagination?.pages ?? 1);
      setTotalResults(payload.pagination?.total ?? fetchedResults.length ?? 0);
      setHasSearched(true);
      setLastQuery({ keyword: formKeyword, location: formLocation });
    } catch (err) {
      setError(err.message);
      setResults([]);
      setTotalPages(1);
      setTotalResults(0);
    } finally {
      setLoading(false);
    }
  };

  // Soumission principale du formulaire (réinitialise à la première page)
  const handleSubmit = (event) => {
    event.preventDefault();
    setPage(1);
    fetchResults(1, { keyword, location });
  };

  // Navigation entre les pages en réutilisant la dernière requête exécutée
  const handlePageChange = (newPage) => {
    if (newPage === page) {
      return;
    }
    setPage(newPage);
    fetchResults(newPage, lastQuery);
  };

  return (
    <div className="min-h-screen bg-slate-100 pb-16">
      <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <header className="pt-12 text-center text-slate-900">
          <span className="inline-flex items-center gap-2 rounded-full bg-white px-4 py-1 text-sm font-medium text-sky-700 shadow">
            Trouvez, comparez, contactez
          </span>
          <h1 className="mt-6 text-4xl font-semibold leading-tight sm:text-5xl lg:text-6xl gradient-text">
            Explorez les commerces près de chez vous
          </h1>
          <p className="mt-4 text-lg text-slate-600">
            Restaurants, artisans, services… trouvez votre prochain coup de cœur en quelques secondes.
          </p>
        </header>

        <section className="relative mt-10">
          <div className="absolute inset-0 blur-3xl opacity-20 bg-gradient-to-r from-sky-200 via-blue-200 to-indigo-100" />
          <div className="relative glass rounded-3xl p-6 md:p-8">
            <SearchForm
              keyword={keyword}
              location={location}
              onKeywordChange={setKeyword}
              onLocationChange={setLocation}
              onSubmit={handleSubmit}
              loading={loading}
            />
            {error && (
              <p className="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {error}
              </p>
            )}
          </div>
        </section>

        <section className="mt-10 grid gap-4 sm:grid-cols-3">
          <article className="glass rounded-2xl p-5 text-center text-slate-700">
            <p className="text-sm uppercase tracking-[0.2em] text-sky-500">Résultats</p>
            <p className="mt-2 text-3xl font-semibold text-slate-900">{totalResults}</p>
            {hasSearched && (
              <p className="text-sm text-slate-500">
                {lastQuery.keyword} à {lastQuery.location}
              </p>
            )}
          </article>
          <article className="glass rounded-2xl p-5 text-center text-slate-700">
            <p className="text-sm uppercase tracking-[0.2em] text-sky-500">Pages</p>
            <p className="mt-2 text-3xl font-semibold text-slate-900">
              {page}/{totalPages}
            </p>
            <p className="text-sm text-slate-500">10 commerces par page</p>
          </article>
          <article className="glass rounded-2xl p-5 text-center text-slate-700">
            <p className="text-sm uppercase tracking-[0.2em] text-sky-500">Temps réel</p>
            <p className="mt-2 text-3xl font-semibold text-slate-900">{loading ? '⏳' : '⚡️'}</p>
            <p className="text-sm text-slate-500">
              {loading ? 'Recherche en cours' : hasSearched ? 'À jour' : 'Prêt à lancer'}
            </p>
          </article>
        </section>

        <Results items={results} loading={loading} total={totalResults} hasSearched={hasSearched} />

        <Pagination page={page} pages={totalPages} onChange={handlePageChange} />
      </div>
    </div>
  );
}

export default App;

