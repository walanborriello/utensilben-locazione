import 'select2/dist/css/select2.min.css';
import './styles/app.css';
import $ from 'jquery';
import inizializzaSelect2Plugin from 'select2';

// select2 e' distribuito in stile UMD "vecchio": il bundle ESM servito da
// jsDelivr non lo auto-esegue sull'import, va invocato a mano passandogli
// jQuery, altrimenti $.fn.select2 non esiste ("... .select2 is not a function").
inizializzaSelect2Plugin(window, $);

const input = document.querySelector('[data-ub-search-input]');
const resultsBox = document.querySelector('[data-ub-search-results]');
const overlay = document.querySelector('[data-ub-modal-overlay]');
const modalTitolo = document.querySelector('[data-ub-modal-titolo]');
const modalCodice = document.querySelector('[data-ub-modal-codice]');
const modalScaffale = document.querySelector('[data-ub-modal-scaffale]');
const modalLabel = document.querySelector('[data-ub-modal-label]');
const modalAltre = document.querySelector('[data-ub-modal-altre]');
const modalAltreLista = document.querySelector('[data-ub-modal-altre-lista]');
const closeButtons = document.querySelectorAll('[data-ub-modal-chiudi]');

let posizioniCorrenti = [];
let debounceTimer = null;

function debounce(fn, delay) {
    return (...args) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fn(...args), delay);
    };
}

function nascondiRisultati() {
    resultsBox.hidden = true;
    resultsBox.innerHTML = '';
}

function mostraStato(messaggio) {
    resultsBox.hidden = false;
    resultsBox.innerHTML = `<div class="ub-search__loading">${messaggio}</div>`;
}

async function cerca(termine) {
    if (termine.trim().length === 0) {
        nascondiRisultati();
        return;
    }

    mostraStato('Ricerca in corso…');

    try {
        const risposta = await fetch(`/api/cerca?q=${encodeURIComponent(termine)}`);
        const risultati = await risposta.json();
        renderRisultati(risultati);
    } catch (errore) {
        resultsBox.hidden = false;
        resultsBox.innerHTML = '<div class="ub-search__empty">Errore durante la ricerca. Riprova.</div>';
    }
}

function renderRisultati(risultati) {
    if (!Array.isArray(risultati) || risultati.length === 0) {
        resultsBox.hidden = false;
        resultsBox.innerHTML = '<div class="ub-search__empty">Nessun materiale trovato.</div>';
        return;
    }

    resultsBox.innerHTML = '';
    risultati.forEach((materiale) => {
        const bottone = document.createElement('button');
        bottone.type = 'button';
        bottone.className = 'ub-result';
        bottone.innerHTML = `
            <span>
                <span class="ub-result__nome">${escapeHtml(materiale.nome)}</span>
                <div class="ub-result__meta">${escapeHtml(materiale.codice)}${materiale.categoria ? ' · ' + escapeHtml(materiale.categoria) : ''}</div>
            </span>
            <span class="ub-result__badge">${materiale.numeroPosizioni > 0 ? 'vedi posizione' : 'senza posizione'}</span>
        `;
        bottone.addEventListener('click', () => apriModale(materiale.id));
        resultsBox.appendChild(bottone);
    });
    resultsBox.hidden = false;
}

function escapeHtml(valore) {
    const div = document.createElement('div');
    div.textContent = valore ?? '';
    return div.innerHTML;
}

async function apriModale(materialeId) {
    try {
        const risposta = await fetch(`/api/materiali/${materialeId}/posizione`);
        if (!risposta.ok) {
            throw new Error('Materiale non trovato');
        }
        const dati = await risposta.json();
        nascondiRisultati();
        popolaModale(dati);
        overlay.hidden = false;
        document.body.style.overflow = 'hidden';
    } catch (errore) {
        alert('Non è stato possibile recuperare la posizione del materiale.');
    }
}

function popolaModale(dati) {
    modalTitolo.textContent = dati.materiale.nome;
    modalCodice.textContent = dati.materiale.codice;
    posizioniCorrenti = dati.posizioni ?? [];

    if (posizioniCorrenti.length === 0) {
        modalLabel.innerHTML = 'Nessuna posizione assegnata a questo materiale.';
        modalScaffale.innerHTML = '';
        modalAltre.hidden = true;
        return;
    }

    renderPosizione(0);
    renderChipAltrePosizioni();
}

function renderPosizione(indice) {
    const posizione = posizioniCorrenti[indice];
    if (!posizione) {
        return;
    }

    modalLabel.innerHTML = costruisciBreadcrumb(posizione);
    modalScaffale.innerHTML = costruisciScaffale(posizione.aree);
}

/**
 * Costruisce la fila di "pillole" sopra lo schema (Fila / Lato / Piano /
 * Sezione), con le ultime due evidenziate in arancione perché sono i due
 * dati che si ritrovano anche colorati nel disegno sotto.
 */
function costruisciBreadcrumb(posizione) {
    const pezzi = [
        { testo: `Fila ${posizione.fila.codice}`, accento: false },
        { testo: posizione.areaCorrente.latoLabel, accento: false },
        { testo: `Piano ${posizione.pianoCorrente.numero}`, accento: true },
    ];

    if (posizione.sezione) {
        pezzi.push({ testo: `Sezione ${posizione.sezione}`, accento: true });
    }

    return pezzi
        .map((pezzo, indice) => {
            const separatore = indice === 0 ? '' : '<span class="ub-breadcrumb__sep">›</span>';
            const classe = `ub-breadcrumb__chip${pezzo.accento ? ' ub-breadcrumb__chip--accento' : ''}`;

            return `${separatore}<span class="${classe}">${escapeHtml(pezzo.testo)}</span>`;
        })
        .join('');
}

function renderChipAltrePosizioni() {
    if (posizioniCorrenti.length <= 1) {
        modalAltre.hidden = true;
        return;
    }

    modalAltre.hidden = false;
    modalAltreLista.innerHTML = '';

    posizioniCorrenti.forEach((posizione, indice) => {
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'ub-chip';
        chip.setAttribute('aria-pressed', indice === 0 ? 'true' : 'false');
        const sezioneChip = posizione.sezione ? ` · Sezione ${escapeHtml(posizione.sezione)}` : '';
        chip.innerHTML = `${posizione.principale ? '<span class="ub-chip__stella">★</span> ' : ''}Fila ${escapeHtml(posizione.fila.codice)} · ${escapeHtml(posizione.areaCorrente.latoLabel)} · Piano ${posizione.pianoCorrente.numero}${sezioneChip}`;
        chip.addEventListener('click', () => {
            modalAltreLista.querySelectorAll('.ub-chip').forEach((c) => c.setAttribute('aria-pressed', 'false'));
            chip.setAttribute('aria-pressed', 'true');
            renderPosizione(indice);
        });
        modalAltreLista.appendChild(chip);
    });
}

/**
 * Percorso di un'icona a goccia (segnaposto), viewBox 0 0 24 24, riusato per
 * marcare il vano trovato nello schema dello scaffale.
 */
const UB_ICONA_SEGNAPOSTO = 'M12 2C7.6 2 4 5.6 4 10c0 5.5 6.5 11.3 7.1 11.8.5.5 1.3.5 1.8 0C13.5 21.3 20 15.5 20 10c0-4.4-3.6-8-8-8zm0 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6z';

/**
 * Costruisce lo schema dello scaffale in stile "mobile con vani": una
 * colonna per area (una sola se la fila e' "unica", due affiancate se e'
 * "divisa"), i piani impilati dall'alto in basso come in un mobile reale, e
 * il vano giusto evidenziato con una scheda arancione con segnaposto ed
 * eventuale sezione — cosi' da distinguere a colpo d'occhio i vani vuoti da
 * quello cercato.
 *
 * E' HTML/CSS piano, non SVG: ogni riga e' un normale div con flexbox, quindi
 * si centra da solo senza bisogno di calcolare coordinate a mano.
 */
function costruisciScaffale(aree) {
    // Se una colonna ha meno piani delle altre, le mancano dei vani in cima
    // (i piani reali sono allineati in basso): aggiungiamo righe vuote sopra,
    // cosi' tutte le colonne restano alte uguali invece di accorciarsi.
    const righeMax = Math.max(...aree.map((area) => area.piani.length));

    return aree
        .map((area) => {
            const piani = [...area.piani].sort((a, b) => b.numero - a.numero);
            const righeVuote = '<div class="ub-scaffale__riga ub-scaffale__riga--vuota"></div>'.repeat(righeMax - piani.length);
            const righe = piani.map((piano) => costruisciRigaScaffale(piano)).join('');

            return `
                <div class="ub-scaffale__colonna">
                    <span class="ub-scaffale__lato">${escapeHtml(area.latoLabel.toUpperCase())}</span>
                    <div class="ub-scaffale__mobile">${righeVuote}${righe}</div>
                </div>`;
        })
        .join('');
}

function costruisciRigaScaffale(piano) {
    if (!piano.evidenziato) {
        return `
            <div class="ub-scaffale__riga">
                <span class="ub-scaffale__numero">${piano.numero}</span>
                <span class="ub-scaffale__testo">Piano ${piano.numero}${piano.etichetta ? ' · ' + escapeHtml(piano.etichetta) : ''}</span>
            </div>`;
    }

    const sezioneHtml = piano.sezione
        ? `<span class="ub-scaffale__sezione">Sez. ${escapeHtml(piano.sezione)}</span>`
        : '';

    return `
        <div class="ub-scaffale__riga ub-scaffale__riga--evidenziato">
            <div class="ub-scaffale__scheda">
                <svg class="ub-scaffale__scheda-icona" viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="${UB_ICONA_SEGNAPOSTO}" fill="#ffffff" /></svg>
                <span class="ub-scaffale__scheda-testo">Piano ${piano.numero}</span>
                ${sezioneHtml}
            </div>
        </div>`;
}

function chiudiModale() {
    overlay.hidden = true;
    document.body.style.overflow = '';
}

if (input) {
    input.addEventListener('input', debounce((event) => cerca(event.target.value), 250));
    input.addEventListener('focus', () => {
        if (resultsBox.innerHTML.trim() !== '') {
            resultsBox.hidden = false;
        }
    });
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.ub-search')) {
            nascondiRisultati();
        }
    });
}

closeButtons.forEach((bottone) => bottone.addEventListener('click', chiudiModale));
overlay?.addEventListener('click', (event) => {
    if (event.target === overlay) {
        chiudiModale();
    }
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !overlay.hidden) {
        chiudiModale();
    }
});

/**
 * Righe aggiungibili/rimovibili nel form prodotto (le posizioni sullo
 * scaffale): usa il prototipo generato da Symfony per il CollectionType,
 * sostituendo "__name__" con un indice progressivo ad ogni riga aggiunta.
 */
document.querySelectorAll('[data-collezione-wrapper]').forEach((wrapper) => {
    const lista = wrapper.querySelector('[data-collezione-lista]');
    const bottoneAggiungi = wrapper.querySelector('[data-collezione-aggiungi]');
    let indice = parseInt(wrapper.dataset.indice, 10);

    function attivaRimozione(riga) {
        riga.querySelector('[data-collezione-rimuovi]')?.addEventListener('click', () => {
            riga.remove();
            if (lista.querySelectorAll('[data-collezione-riga]').length === 0) {
                const vuota = document.createElement('p');
                vuota.className = 'ub-collezione__vuota';
                vuota.setAttribute('data-collezione-vuota', '');
                vuota.textContent = 'Nessuna posizione assegnata.';
                lista.appendChild(vuota);
            }
        });
    }

    lista.querySelectorAll('[data-collezione-riga]').forEach(attivaRimozione);

    bottoneAggiungi?.addEventListener('click', () => {
        lista.querySelector('[data-collezione-vuota]')?.remove();

        const riga = document.createElement('div');
        riga.className = 'ub-posizione-riga';
        riga.setAttribute('data-collezione-riga', '');
        riga.innerHTML = wrapper.dataset.prototipo.replace(/__name__/g, indice)
            + '<button type="button" class="ub-btn ub-btn--small ub-btn--danger" data-collezione-rimuovi>Rimuovi</button>';
        lista.appendChild(riga);
        attivaRimozione(riga);
        inizializzaSelect2(riga.querySelectorAll('select'));
        indice++;
    });
});

/**
 * Tutte le select diventano Select2 (ricercabili). Va richiamata anche sulle
 * select delle righe aggiunte dinamicamente (posizioni sullo scaffale), che
 * non esistono ancora al caricamento della pagina.
 */
function inizializzaSelect2(selects) {
    $(selects).select2({ width: '100%' });
}

inizializzaSelect2(document.querySelectorAll('select'));

/**
 * Paginazione client-side per le liste "a card" in gestione: mostra solo
 * "per-pagina" elementi alla volta e aggiunge i controlli prec/succ. Se gli
 * elementi sono meno del limite non fa nulla (restano tutti visibili, niente
 * controlli) — utile anche come fallback se per qualche motivo il JS non
 * gira: senza hidden impostato lato server, si vede comunque tutta la lista.
 *
 * Il numero di elementi per pagina dipende dalla griglia: 3 colonne x 2 righe
 * (6) da desktop, 1 colonna x 5 righe (5) sotto i 480px — stessa soglia dei
 * `@media` in app.css che cambiano il numero di colonne di .ub-cards.
 */
const ub_query_mobile = window.matchMedia('(max-width: 480px)');

document.querySelectorAll('[data-ub-paginazione]').forEach((wrapper) => {
    function perPaginaCorrente() {
        return ub_query_mobile.matches
            ? (parseInt(wrapper.dataset.perPagina, 10) || 5)
            : (parseInt(wrapper.dataset.perPaginaDesktop, 10) || 6);
    }

    const elementi = Array.from(wrapper.querySelectorAll('[data-ub-paginazione-item]'));
    const controlli = wrapper.querySelector('[data-ub-paginazione-controlli]');

    if (!controlli) {
        return;
    }

    let perPagina = perPaginaCorrente();
    let totalePagine = Math.ceil(elementi.length / perPagina);
    let paginaCorrente = 1;

    if (elementi.length <= perPagina) {
        return;
    }

    ub_query_mobile.addEventListener('change', () => {
        perPagina = perPaginaCorrente();
        totalePagine = Math.ceil(elementi.length / perPagina);
        mostraPagina(1);
    });

    function mostraPagina(pagina) {
        paginaCorrente = Math.min(Math.max(1, pagina), totalePagine);
        elementi.forEach((elemento, indice) => {
            const primoDellaPagina = (paginaCorrente - 1) * perPagina;
            elemento.hidden = indice < primoDellaPagina || indice >= primoDellaPagina + perPagina;
        });
        disegnaControlli();
    }

    function disegnaControlli() {
        controlli.innerHTML = '';

        const precedente = document.createElement('button');
        precedente.type = 'button';
        precedente.className = 'ub-btn ub-btn--small';
        precedente.textContent = '‹ Precedente';
        precedente.disabled = paginaCorrente === 1;
        precedente.addEventListener('click', () => mostraPagina(paginaCorrente - 1));

        const info = document.createElement('span');
        info.className = 'ub-paginazione__info';
        info.textContent = `Pagina ${paginaCorrente} di ${totalePagine}`;

        const successiva = document.createElement('button');
        successiva.type = 'button';
        successiva.className = 'ub-btn ub-btn--small';
        successiva.textContent = 'Successiva ›';
        successiva.disabled = paginaCorrente === totalePagine;
        successiva.addEventListener('click', () => mostraPagina(paginaCorrente + 1));

        controlli.append(precedente, info, successiva);
    }

    mostraPagina(1);
});
