# UtensilBen — Localizzazione materiali

Sito interno per trovare in pochi secondi dove si trova un materiale a magazzino (fila, area/lato, piano), con gestione CRUD della struttura e del catalogo. MVP senza login, come richiesto.

> Nota: Claude lavora da un ambiente cloud isolato e non ha accesso diretto a Docker Desktop né al file hosts del tuo PC (che richiede permessi di Amministratore) — per questo l'ultimo miglio (avviare i container, aggiungere la voce al file hosts) va lanciato da qui, sul tuo computer. Per ridurlo al minimo, tutti i passaggi sono raccolti nello script `avvia.ps1` qui sotto: un comando solo.

## Avvio in locale (un solo comando)

Prerequisito: Docker Desktop avviato.

Da questa cartella, in PowerShell:

```
.\avvia.ps1
```

Lo script aggiunge la voce `locazione.utensilben.local` al file hosts di Windows, builda e avvia i container Docker, crea il database, genera ed esegue la migrazione e carica i dati di prova. Se PowerShell blocca l'esecuzione degli script (criterio di sicurezza di Windows), usa invece:

```
powershell -ExecutionPolicy Bypass -File avvia.ps1
```

**Nota sul file hosts:** modificarlo richiede i permessi di Amministratore. Se avvii PowerShell normalmente lo script prova comunque, e se non riesce te lo segnala mostrando la riga esatta da aggiungere a mano (oppure rilancia lo script da PowerShell aperto come Amministratore — tasto destro sull'icona > "Esegui come amministratore" — perché la modifica avvenga in automatico).

La prima build scarica ed installa le dipendenze PHP (Symfony, Doctrine): può richiedere qualche minuto, serve una connessione internet normale sulla macchina.

> Il back-office è realizzato con controller e form Symfony "a mano" (niente EasyAdminBundle): la versione più recente della libreria si è rivelata instabile con lo stack Doctrine ORM 3 usato qui (più bug nel rendering dei pulsanti), quindi si è scelta questa strada, più semplice da mantenere per 4-6 sezioni CRUD.

Se preferisci lanciare i passaggi a mano invece dello script, sono questi:

```
docker compose up --build -d
docker compose exec php php bin/console doctrine:database:create --if-not-exists
docker compose exec php php bin/console make:migration --no-interaction
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec php php bin/console doctrine:fixtures:load --no-interaction
```

L'ultimo comando carica dati di prova (3 file, alcuni piani, una decina di materiali con codici EAN e un caso con doppia posizione) per poter provare subito la ricerca.

## Indirizzi

- Sito pubblico (ricerca): http://locazione.utensilben.local
- Back-office (gestione File, Piani, Materiali, Codici, Posizionamenti): http://locazione.utensilben.local/admin
- Adminer (ispezione database): http://localhost:8081 — server `database`, utente `utensilben`, password `utensilben`, database `utensilben`

Se sul PC la porta 80 è già occupata da un altro programma, in `docker-compose.yml` cambia la riga `"80:80"` in `"8080:80"` e visita `http://locazione.utensilben.local:8080`.

## Test

```
docker compose exec php php bin/phpunit
```

Coprono la logica di generazione automatica delle aree (fila unica/divisa) e la risoluzione della posizione di un materiale (fila/area/piano, inclusi i casi multi-posizione).

## Cose da sapere

Le dipendenze PHP (`vendor/`) vivono in un volume Docker separato, non nella cartella del progetto: questo evita che, montando la cartella dal tuo PC dentro al container, si "nasconda" quanto installato durante la build. Ha una conseguenza pratica: se in futuro aggiungi una dipendenza in `composer.json`, dopo aver rifatto la build esegui anche `docker compose exec php composer install`, altrimenti il volume resta con le dipendenze vecchie.

Le Aree (lato sinistro/destro o fila unica) non si creano mai a mano: si generano automaticamente quando crei o modifichi una Fila, in base al tipo scelto.

## Prossimo passo

Non ancora incluso in questa prima versione: l'import del catalogo da file (CSV/Excel) con riconoscimento dei codici EAN, previsto nella specifica di progetto. Verrà aggiunto in un secondo momento, una volta collaudata questa base.

## Produzione

Ambiente di produzione non ancora deciso: l'app userà probabilmente le stesse immagini Docker anche lì (su un VPS, con `docker compose`), ma questa parte è rimandata a dopo i test locali.
