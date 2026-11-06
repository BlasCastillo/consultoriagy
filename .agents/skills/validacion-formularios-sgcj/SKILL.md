---
name: validacion-formularios-sgcj
description: Estándar obligatorio para validaciones Frontend (global-validation.js + HTML5 + Blade @error/old) y Backend (FormRequests / validate) en todos los formularios del SGCJ.
---

# Skill: Validación Integral de Formularios SGCJ

## 1. Clases de `public/js/global-validation.js`
- `.val-numerico`: Cédulas, teléfonos numéricos, años, cantidades enteras, números de ejemplar.
- `.val-solo-letras`: Nombres y apellidos de personas (ciudadanos, gobernadores, solicitantes).
- `.val-texto-limpio`: Textos generales, títulos, sumarios, siglas, nombres de instituciones/sedes/estantes/gavetas, motivos, correos, búsquedas (bloquea `< > ; = { }`).
- `.val-fecha-futura`: Fechas que no permiten días pasados (usando hora local, no UTC).
- `.val-fecha-rango`: Con atributo `data-target="#id_fecha_fin"` para sincronizar el atributo `min` de la fecha destino.

## 2. Requisitos en toda Vista Blade con `<form>`
1. Bloque superior de alerta `@if ($errors->any())` y/o `@if (session('error'))` visible.
2. Preservación de valores con `old('campo', $valorPorDefecto)` en todos los inputs, selects, textareas y reconstrucción de arreglos dinámicos (`old('actos')`).
3. Atributos HTML5 (`required`, `maxlength`, `minlength`, `min`, `max`, `accept`).
4. En formularios enviados mediante `Swal.fire` + `form.submit()`, validar primero `window.validarFormularioSGCJ(form)` antes de abrir la confirmación.
