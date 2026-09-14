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
const modalLabel = document.querySelector('[data-ub-modal-label]');
const modalNota = document.querySelector('[data-ub-modal-nota]');
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
        modalAltre.hidden = true;
        modalNota.hidden = true;
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

    if (posizione.note) {
        modalNota.textContent = posizione.note;
        modalNota.hidden = false;
    } else {
        modalNota.hidden = true;
    }
}

/**
 * Costruisce la fila di "pillole" del percorso (Piano / Fila / Sezione /
 * Ripiano), con le ultime due evidenziate in arancione.
 */
function costruisciBreadcrumb(posizione) {
    const pezzi = [
        { testo: posizione.piano.nome, accento: false },
        { testo: `Fila ${posizione.fila.lettera}`, accento: false },
        { testo: `Sezione ${posizione.sezione.lettera}`, accento: true },
        { testo: `Ripiano ${posizione.ripiano.numero}`, accento: true },
    ];

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
        chip.innerHTML = `${posizione.principale ? '<span class="ub-chip__stella">★</span> ' : ''}${escapeHtml(posizione.piano.nome)} · Fila ${escapeHtml(posizione.fila.lettera)} · Sezione ${escapeHtml(posizione.sezione.lettera)} · Ripiano ${posizione.ripiano.numero}`;
        chip.addEventListener('click', () => {
            modalAltreLista.querySelectorAll('.ub-chip').forEach((c) => c.setAttribute('aria-pressed', 'false'));
            chip.setAttribute('aria-pressed', 'true');
            renderPosizione(indice);
        });
        modalAltreLista.appendChild(chip);
    });
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
        inizializzaCascataPosizione(riga);
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

/**
 * Le 4 select "piano/fila/sezione/ripiano" di una riga posizione sono a
 * cascata: piano e fila e sezione sono select finte (non mappate
 * sull'entità, servono solo a restringere le opzioni), solo "ripiano" viene
 * davvero salvato. I dati di tutta la gerarchia sono già negli <option> resi
 * da Symfony (via choice_attr: data-piano-id/data-fila-id/data-sezione-id),
 * niente bisogno di un'altra chiamata al server.
 */
function opzioniComplete(select) {
    if (!select._opzioniComplete) {
        select._opzioniComplete = Array.from(select.options).map((opzione) => opzione.cloneNode(true));
    }
    return select._opzioniComplete;
}

function popolaSelect(select, opzioni, valoreSelezionato) {
    const eraSelect2 = $(select).hasClass('select2-hidden-accessible');
    if (eraSelect2) {
        $(select).select2('destroy');
    }
    select.innerHTML = '';
    opzioni.forEach((opzione) => select.appendChild(opzione.cloneNode(true)));
    select.value = valoreSelezionato ?? '';
    if (eraSelect2) {
        inizializzaSelect2(select);
    }
}

function filtraOpzioni(opzioni, attributo, valore) {
    const placeholder = opzioni.filter((opzione) => opzione.value === '');
    if (!valore) {
        return placeholder;
    }

    return [...placeholder, ...opzioni.filter((opzione) => opzione.dataset[attributo] === valore)];
}

function inizializzaCascataPosizione(riga) {
    const selectPiano = riga.querySelector('select[name$="[piano]"]');
    const selectFila = riga.querySelector('select[name$="[fila]"]');
    const selectSezione = riga.querySelector('select[name$="[sezione]"]');
    const selectRipiano = riga.querySelector('select[name$="[ripiano]"]');

    if (!selectPiano || !selectFila || !selectSezione || !selectRipiano) {
        return;
    }

    const tutteFila = opzioniComplete(selectFila);
    const tutteSezione = opzioniComplete(selectSezione);
    const tutteRipiano = opzioniComplete(selectRipiano);

    // Se la riga arriva già valorizzata (modifica di un prodotto esistente),
    // "ripiano" ha già l'opzione giusta: risaliamo la gerarchia dai suoi
    // data-* per preselezionare piano/fila/sezione, che sono select finte e
    // Symfony non le valorizza da sé.
    const ripianoIniziale = tutteRipiano.find((opzione) => opzione.value === selectRipiano.value && opzione.value !== '');

    selectPiano.value = ripianoIniziale?.dataset.pianoId ?? '';
    popolaSelect(selectFila, filtraOpzioni(tutteFila, 'pianoId', selectPiano.value || null), ripianoIniziale?.dataset.filaId);
    popolaSelect(selectSezione, filtraOpzioni(tutteSezione, 'filaId', selectFila.value || null), ripianoIniziale?.dataset.sezioneId);
    popolaSelect(selectRipiano, filtraOpzioni(tutteRipiano, 'sezioneId', selectSezione.value || null), selectRipiano.value);

    // Select2 cambia il valore del <select> chiamando $(...).trigger('change')
    // via jQuery: non genera un evento nativo che un addEventListener('change')
    // possa intercettare. Bisogna quindi agganciarsi con $(...).on('change').
    $(selectPiano).on('change', () => {
        popolaSelect(selectFila, filtraOpzioni(tutteFila, 'pianoId', selectPiano.value || null), null);
        popolaSelect(selectSezione, filtraOpzioni(tutteSezione, 'filaId', null), null);
        popolaSelect(selectRipiano, filtraOpzioni(tutteRipiano, 'sezioneId', null), null);
    });
    $(selectFila).on('change', () => {
        popolaSelect(selectSezione, filtraOpzioni(tutteSezione, 'filaId', selectFila.value || null), null);
        popolaSelect(selectRipiano, filtraOpzioni(tutteRipiano, 'sezioneId', null), null);
    });
    $(selectSezione).on('change', () => {
        popolaSelect(selectRipiano, filtraOpzioni(tutteRipiano, 'sezioneId', selectSezione.value || null), null);
    });
}

document.querySelectorAll('.ub-posizione-riga').forEach(inizializzaCascataPosizione);
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
