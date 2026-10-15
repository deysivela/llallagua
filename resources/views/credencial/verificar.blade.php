<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verificación de Credencial — G.A.M. Llallagua</title>
  <style>
    :root {
      --green: #005029;
      --green-light: #147b14;
      --green-soft: #e8f0ea;
      --border: #d8e0d8;
      --text: #1a1a1a;
      --text-muted: #5c6b5c;
      --danger: #b91c1c;
      --danger-soft: #fbeaea;
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      min-height: 100vh;
      display: grid;
      place-items: center;
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text);
      background: linear-gradient(145deg, #e8ece8 0%, #f5f7f5 100%);
      padding: 1.5rem;
    }

    .card {
      width: 100%;
      max-width: 520px;
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 16px;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06), 0 12px 32px rgba(0, 0, 0, 0.08);
      overflow: hidden;
    }

    .card__head {
      background: var(--green);
      color: #fff;
      padding: 1.5rem 1.75rem;
    }

    .card__head h1 {
      margin: 0 0 0.2rem;
      font-size: 1.05rem;
      letter-spacing: 0.04em;
      text-transform: uppercase;
    }

    .card__head p {
      margin: 0;
      font-size: 0.8rem;
      opacity: 0.85;
    }

    .card__body {
      padding: 1.75rem;
    }

    .status {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      padding: 0.9rem 1rem;
      border-radius: 10px;
      font-weight: 700;
      font-size: 0.95rem;
      margin-bottom: 1.5rem;
    }

    .status--ok { background: var(--green-soft); color: var(--green); }
    .status--bad { background: var(--danger-soft); color: var(--danger); }

    .status__dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      flex-shrink: 0;
    }

    .status--ok .status__dot { background: var(--green); }
    .status--bad .status__dot { background: var(--danger); }

    .photo {
      max-width: 200px;
      margin: 0 auto 1.5rem;
      border-radius: 10px;
      overflow: hidden;
      border: 3px solid var(--green);
    }

    .photo img {
      width: 100%;
      display: block;
    }

    .row {
      display: flex;
      justify-content: space-between;
      gap: 1rem;
      padding: 0.7rem 0;
      border-bottom: 1px solid var(--border);
      font-size: 0.9rem;
    }

    .row:last-of-type { border-bottom: none; }

    .row dt { color: var(--text-muted); flex-shrink: 0; }

    .row dd {
      margin: 0;
      text-align: right;
      font-weight: 700;
      text-transform: uppercase;
      word-break: break-word;
    }

    .foot {
      margin-top: 1.5rem;
      text-align: center;
      font-size: 0.72rem;
      color: var(--text-muted);
    }

    .foot strong { color: var(--green); }
  </style>
</head>
<body>
  <div class="card">
    <div class="card__head">
      <h1>Gobierno Autónomo Municipal</h1>
      <p>de Llallagua · Verificación digital de credencial</p>
    </div>
    <div class="card__body">
      @if ($credencial)
        <div class="status status--ok">
          <span class="status__dot"></span>
          Credencial auténtica · registrada en el sistema
        </div>

        @if ($credencial->foto)
          <div class="photo">
            <img src="{{ $credencial->foto }}" alt="Foto del portador">
          </div>
        @endif

        <dl>
          <div class="row"><dt>Nro. de registro</dt><dd>{{ $credencial->id }}</dd></div>
          <div class="row"><dt>Código</dt><dd>{{ $credencial->hash }}</dd></div>
          <div class="row"><dt>Portador</dt><dd>{{ $credencial->nombre }}</dd></div>
          <div class="row"><dt>Cargo</dt><dd>{{ $credencial->cargo }}</dd></div>
          <div class="row"><dt>Fecha de emisión</dt><dd>{{ $credencial->fecha ? $credencial->fecha->format('d/m/Y') : '—' }}</dd></div>
        </dl>
      @else
        <div class="status status--bad">
          <span class="status__dot"></span>
          Credencial no encontrada en el sistema
        </div>
        <p style="margin:0;color:#5c6b5c;font-size:0.85rem;">
          Comunicate con el Gobierno Autónomo Municipal de Llallagua si considerás que esto es un error.
        </p>
      @endif

      <p class="foot">Emitido por <strong>G.A.M. Llallagua</strong> · este documento es intransferible y de uso exclusivo del portador.</p>
    </div>
  </div>
</body>
</html>