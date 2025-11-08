#!/bin/bash
# Script para ejecutar todas las pruebas

echo "========================================"
echo "SISTEMA DE GESTIÓN DE INVENTARIO - TESTS"
echo "========================================"
echo ""

echo "Ejecutando pruebas unitarias..."
echo ""

php ../unit/ProductoTest.php
echo ""
php ../unit/InventarioTest.php
echo ""

echo "========================================"
echo "RESUMEN DE PRUEBAS COMPLETADAS"
echo "========================================"
