# Публикация ModaStyle на GitHub (masak3724-crypto / masak3724@gmail.com)
$ErrorActionPreference = "Stop"
$git = "C:\Program Files\Git\bin\git.exe"
$repoRoot = Split-Path $PSScriptRoot -Parent
Set-Location $repoRoot

$owner = "masak3724-crypto"
$repoName = "magashop"
$remoteUrl = "https://github.com/$owner/$repoName.git"

$env:GIT_AUTHOR_NAME = "masak3724-crypto"
$env:GIT_AUTHOR_EMAIL = "masak3724@gmail.com"
$env:GIT_COMMITTER_NAME = "masak3724-crypto"
$env:GIT_COMMITTER_EMAIL = "masak3724@gmail.com"

gh auth status 2>$null
if ($LASTEXITCODE -ne 0) {
    Write-Host "Войдите в GitHub..."
    gh auth login -h github.com -p https -w
}

$remotes = & $git remote 2>$null
if ($remotes -notcontains "origin") {
    & $git remote add origin $remoteUrl
    Write-Host "Добавлен remote: $remoteUrl"
}

$repoExists = $false
gh repo view "$owner/$repoName" 2>$null | Out-Null
if ($LASTEXITCODE -eq 0) {
    $repoExists = $true
    Write-Host "Репозиторий уже существует: https://github.com/$owner/$repoName"
}

if (-not $repoExists) {
    gh repo create $repoName --public --description='ModaStyle fashion store on Laravel'
    & $git remote add origin $remoteUrl 2>$null
}

& $git branch -M main 2>$null
& $git push -u origin main

Write-Host ""
Write-Host "Готово: https://github.com/$owner/$repoName"
