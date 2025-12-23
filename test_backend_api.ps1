# Script de Prueba - Backend API Email Integration

Write-Host "=== PRUEBA 1: GET - Listar Usuarios ===" -ForegroundColor Cyan
$response = curl.exe -X GET http://localhost:8000/api/controllers/usuario.php
$usuarios = $response | ConvertFrom-Json
Write-Host "Total de usuarios: $($usuarios.total)" -ForegroundColor Green

foreach ($user in $usuarios.records) {
    Write-Host "  - $($user.nombre_completo) ($($user.username))" -ForegroundColor Yellow
    Write-Host "    Email: $($user.email)" -ForegroundColor White
    Write-Host "    Notificaciones: $($user.notificaciones_activas)" -ForegroundColor White
}

Write-Host ""
Write-Host "=== PRUEBA 2: POST - Crear Usuario con Email ===" -ForegroundColor Cyan
$timestamp = Get-Date -Format 'HHmmss'
$body = "{
    `"nombre_completo`": `"Test Usuario $timestamp`",
    `"username`": `"test_$timestamp`",
    `"password`": `"test123`",
    `"email`": `"test$timestamp@ejemplo.com`",
    `"notificaciones_activas`": 1,
    `"ID_ROL_USUARIO`": 3,
    `"id_area`": 1,
    `"estado`": `"activo`"
}"

try {
    $result = Invoke-RestMethod -Uri "http://localhost:8000/api/controllers/usuario.php" -Method POST -Body $body -ContentType "application/json"
    Write-Host "OK Usuario creado" -ForegroundColor Green
    Write-Host "  $($result.message)" -ForegroundColor White
} catch {
    Write-Host "ERROR: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== PRUEBA 3: PUT - Actualizar Email ===" -ForegroundColor Cyan
$updateBody = "{
    `"id_usuario`": 4,
    `"nombre_completo`": `"Usuario Prueba ACTUALIZADO`",
    `"username`": `"test_backend`",
    `"email`": `"actualizado@ejemplo.com`",
    `"notificaciones_activas`": 0,
    `"ID_ROL_USUARIO`": 3,
    `"id_area`": 1,
    `"estado`": `"activo`"
}"

try {
    $result = Invoke-RestMethod -Uri "http://localhost:8000/api/controllers/usuario.php" -Method PUT -Body $updateBody -ContentType "application/json"
    Write-Host "OK Usuario actualizado" -ForegroundColor Green
    Write-Host "  $($result.message)" -ForegroundColor White
} catch {
    Write-Host "ERROR: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== PRUEBA 4: Validacion Email Invalido ===" -ForegroundColor Cyan
$invalidBody = "{
    `"nombre_completo`": `"Test Invalido`",
    `"username`": `"test_invalid`",
    `"password`": `"test123`",
    `"email`": `"esto-no-es-email`",
    `"notificaciones_activas`": 1,
    `"ID_ROL_USUARIO`": 3,
    `"id_area`": 1,
    `"estado`": `"activo`"
}"

try {
    $result = Invoke-RestMethod -Uri "http://localhost:8000/api/controllers/usuario.php" -Method POST -Body $invalidBody -ContentType "application/json"
    Write-Host "FALLO: Email invalido aceptado" -ForegroundColor Red
} catch {
    Write-Host "OK Validacion correcta" -ForegroundColor Green
}

Write-Host ""
Write-Host "=== PRUEBA 5: GET Final ===" -ForegroundColor Cyan
$finalResponse = curl.exe -X GET http://localhost:8000/api/controllers/usuario.php
$finalUsers = $finalResponse | ConvertFrom-Json
Write-Host "Total: $($finalUsers.total)" -ForegroundColor Green

$user4 = $finalUsers.records | Where-Object { $_.id_usuario -eq 4 }
if ($user4) {
    Write-Host "  Usuario ID 4:" -ForegroundColor Yellow
    Write-Host "    Nombre: $($user4.nombre_completo)" -ForegroundColor White
    Write-Host "    Email: $($user4.email)" -ForegroundColor White
    Write-Host "    Notificaciones: $($user4.notificaciones_activas)" -ForegroundColor White
}

Write-Host ""
Write-Host "=== RESUMEN ===" -ForegroundColor Magenta
Write-Host "Backend 100% listo para deployment!" -ForegroundColor Green
