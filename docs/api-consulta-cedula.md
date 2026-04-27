# API Emssanar EPS — Consulta de Afiliado por Cédula

## Descripción

Retorna el **historial completo** de consultas realizadas a Emssanar EPS para un número de cédula específico, ordenado del registro **más reciente al más antiguo**. Solo se incluyen consultas donde el afiliado fue encontrado exitosamente.

---

## Endpoint

```
GET /api/consulta/cedula/{cedula}
```

### Parámetros de ruta

| Parámetro | Tipo   | Requerido | Descripción                     |
|-----------|--------|-----------|---------------------------------|
| `cedula`  | string | Sí        | Número de cédula (solo dígitos) |

### Autenticación

Requiere token **Bearer** de Sanctum en el header de la petición.

```
Authorization: Bearer <token>
```

---

## Ejemplo de petición

```http
GET /api/consulta/cedula/1234567890
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Accept: application/json
```

---

## Respuestas

### 200 — Consulta exitosa

```json
{
  "success": true,
  "message": "Consulta exitosa.",
  "total": 2,
  "data": [
    {
      "cedula": "1234567890",
      "tipo_documento": "CC",
      "primer_nombre": "MARIA",
      "segundo_nombre": "ELENA",
      "primer_apellido": "TORRES",
      "segundo_apellido": "SILVA",
      "departamento": "NARIÑO",
      "municipio": "PASTO",
      "direccion": "CLL 18 # 22-10",
      "regimen": "SUBSIDIADO",
      "estado_afiliado": "ACTIVO",
      "sede": "PASTO",
      "ips": "ESE HOSPITAL DEPARTAMENTAL",
      "celular": "3109876543",
      "telefono_fijo": null,
      "correo": null,
      "poblacion_especial": null,
      "grupo_etnico": null,
      "eps_nombre": "EMSSANAR E.S.S.",
      "fecha_nacimiento": "1990-03-22",
      "edad": 36,
      "sexo": "F",
      "consultado_en": "2026-04-27T10:30:00+00:00"
    },
    {
      "cedula": "1234567890",
      "tipo_documento": "CC",
      "primer_nombre": "MARIA",
      "segundo_nombre": "ELENA",
      "primer_apellido": "TORRES",
      "segundo_apellido": "SILVA",
      "departamento": "NARIÑO",
      "municipio": "PASTO",
      "direccion": "CLL 18 # 22-10",
      "regimen": "SUBSIDIADO",
      "estado_afiliado": "ACTIVO",
      "sede": "PASTO",
      "ips": "ESE HOSPITAL DEPARTAMENTAL",
      "celular": "3109876543",
      "telefono_fijo": null,
      "correo": null,
      "poblacion_especial": null,
      "grupo_etnico": null,
      "eps_nombre": "EMSSANAR E.S.S.",
      "fecha_nacimiento": "1990-03-22",
      "edad": 36,
      "sexo": "F",
      "consultado_en": "2026-03-10T08:00:00+00:00"
    }
  ]
}
```

### 404 — Sin resultados

```json
{
  "success": false,
  "message": "No se encontraron resultados para la cédula proporcionada.",
  "data": null
}
```

---

## Descripción de campos del JSON de respuesta

### Nivel raíz

| Campo     | Tipo    | Descripción                                                    |
|-----------|---------|----------------------------------------------------------------|
| `success` | boolean | `true` si la operación fue exitosa, `false` en caso contrario |
| `message` | string  | Mensaje descriptivo del resultado                              |
| `total`   | integer | Cantidad total de registros retornados                         |
| `data`    | array   | Arreglo de objetos con el historial de consultas               |

### Objeto dentro de `data[]`

| Campo               | Tipo            | Descripción                                                                       |
|---------------------|-----------------|-----------------------------------------------------------------------------------|
| `cedula`            | string          | Número de documento del afiliado                                                  |
| `tipo_documento`    | string / null   | Tipo de documento (ej. `CC`, `TI`, `CE`, `PA`)                                   |
| `primer_nombre`     | string / null   | Primer nombre del afiliado                                                        |
| `segundo_nombre`    | string / null   | Segundo nombre del afiliado. Puede ser `null`                                     |
| `primer_apellido`   | string / null   | Primer apellido del afiliado                                                      |
| `segundo_apellido`  | string / null   | Segundo apellido del afiliado. Puede ser `null`                                   |
| `departamento`      | string / null   | Departamento de residencia registrado                                             |
| `municipio`         | string / null   | Municipio de residencia registrado                                                |
| `direccion`         | string / null   | Dirección de residencia registrada                                                |
| `regimen`           | string / null   | Régimen de salud (ej. `SUBSIDIADO`, `CONTRIBUTIVO`)                               |
| `estado_afiliado`   | string / null   | Estado actual del afiliado (ej. `ACTIVO`, `RETIRADO`, `SUSPENDIDO`)               |
| `sede`              | string / null   | Sede de Emssanar asignada al afiliado                                             |
| `ips`               | string / null   | IPS primaria asignada al afiliado                                                 |
| `celular`           | string / null   | Número de celular de contacto registrado                                          |
| `telefono_fijo`     | string / null   | Teléfono fijo de contacto. Puede ser `null`                                       |
| `correo`            | string / null   | Correo electrónico de contacto. Puede ser `null`                                  |
| `poblacion_especial`| string / null   | Indica si el afiliado pertenece a alguna población especial. `null` si no aplica  |
| `grupo_etnico`      | string / null   | Grupo étnico del afiliado si aplica. `null` si no aplica                          |
| `eps_nombre`        | string / null   | Nombre oficial de la EPS registrado en el sistema                                 |
| `fecha_nacimiento`  | string / null   | Fecha de nacimiento en formato `YYYY-MM-DD`                                       |
| `edad`              | integer / null  | Edad del afiliado en años calculada al momento de la consulta                     |
| `sexo`              | string / null   | Sexo del afiliado (`M` = Masculino, `F` = Femenino)                               |
| `consultado_en`     | string ISO 8601 | Fecha y hora en que se realizó la consulta (UTC)                                  |

---

## Notas

- Los registros se ordenan de **más reciente a más antiguo** según el campo `consultado_en`.
- Solo se retornan consultas donde el afiliado fue encontrado (`encontrado = true`).
- Si la cédula no tiene registros en la base de datos, se retorna HTTP `404`.
- El campo `cedula` en la URL solo acepta dígitos numéricos; cualquier otro carácter retorna `404` automáticamente.
