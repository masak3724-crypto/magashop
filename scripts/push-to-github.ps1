# Публикация ModaStyle на GitHub (masak3724-crypto / masak3724@gmail.com)
$ErrorActionPreference = "Stop"
$git = "C:\Program Files\Git\bin\git.exe"
$repoRoot = Split-Path $PSScriptRoot -Parent
Set-Location $repoRoot

$env:GIT_AUTHOR_NAME = "masak3724-crypto"
$env:GIT_AUTHOR_EMAIL = "masak3724@gmail.com"
$env:GIT_COMMITTER_NAME = "masak3724-crypto"
$env:GIT_COMMITTER_EMAIL = "masak3724@gmail.com"

# Вход в GitHub (один раз в браузере)
gh auth status 2>$null
if ($LASTEXITCODE -ne 0) {
    Write-Host "Войдите в GitHub..."
    gh auth login -h github.com -p https -w
}

# Создать репозиторий и отправить код
gh repo create magashop --public --source=. --remote=origin --push --description "ModaStyle — интернет-магазин одежды на Laravel"

Write-Host "Готово: https://github.com/masak3724-crypto/magashop"
