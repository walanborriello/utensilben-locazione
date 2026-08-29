# Avvio automatico del sito UtensilBen: aggiunge la voce al file hosts di
# Windows, builda e avvia i container Docker, prepara il database e apre
# il sito nel browser.
#
# Uso: apri questa cartella in PowerShell ed esegui:
#     .\avvia.ps1
#
# Se PowerShell blocca l'esecuzione degli script, usa invece:
#     powershell -ExecutionPolicy Bypass -File avvia.ps1
#
# Per aggiungere automaticamente la voce al file hosts serve avviare
# PowerShell come Amministratore (tasto destro sull'icona di PowerShell >
# "Esegui come amministratore"), altrimenti quel passaggio va fatto a mano
# (lo script te lo segnala e non si blocca).

$hostsPath = "$env:WinDir\System32\drivers\etc\hosts"
$dominio = "locazione.utensilben.local"
$hostEntry = "127.0.0.1 $dominio"

function Step($testo) {
    Write-Host "`n== $testo ==" -ForegroundColor Cyan
}

Step "1. Voce nel file hosts per $dominio"

$hostsOk = $false
$hostsContent = $null
try {
    $hostsContent = Get-Content -Path $hostsPath -ErrorAction Stop
} catch {
    Write-Host "Non riesco a leggere il file hosts ($hostsPath)." -ForegroundColor Yellow
}

if ($null -ne $hostsContent -and ($hostsContent | Select-String -SimpleMatch $dominio)) {
    Write-Host "Voce gia' presente, nessuna modifica necessaria." -ForegroundColor Green
    $hostsOk = $true
} else {
    try {
        Add-Content -Path $hostsPath -Value $hostEntry -ErrorAction Stop
        Write-Host "Aggiunta al file hosts: $hostEntry" -ForegroundColor Green
        $hostsOk = $true
    } catch {
        Write-Host "ATTENZIONE: non sono riuscito a modificare il file hosts (serve eseguire PowerShell come Amministratore)." -ForegroundColor Yellow
        Write-Host "Riavvia questo script con tasto destro > 'Esegui come amministratore', oppure aggiungi a mano questa riga al file $hostsPath :"
        Write-Host "    $hostEntry"
        Write-Host "(puoi aprirlo con: notepad $hostsPath   eseguito come amministratore)"
    }
}

Step "2. Build e avvio dei container Docker"
docker compose up --build -d
if ($LASTEXITCODE -ne 0) {
    Write-Host "Docker Compose ha restituito un errore: controlla che Docker Desktop sia avviato e riprova." -ForegroundColor Red
    exit 1
}

Step "3. Creazione database e schema"
docker compose exec php php bin/console doctrine:database:create --if-not-exists
docker compose exec php php bin/console make:migration --no-interaction
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

Step "4. Caricamento dati di prova"
docker compose exec php php bin/console doctrine:fixtures:load --no-interaction

Step "Fatto"
Write-Host "Sito:        http://$dominio" -ForegroundColor Green
Write-Host "Back-office: http://$dominio/admin" -ForegroundColor Green
Write-Host "Adminer:     http://localhost:8081" -ForegroundColor Green

if ($hostsOk) {
    Start-Process "http://$dominio"
} else {
    Write-Host "`nRicorda: la voce nel file hosts non e' stata aggiunta automaticamente, aggiungila a mano prima di aprire il sito (vedi sopra)." -ForegroundColor Yellow
}
