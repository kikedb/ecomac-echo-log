<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Error Repetido</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="background-color: #fff; padding: 20px; border-radius: 8px;">
        <h2 style="color: #c0392b;">{{ $recurrentError->details->category->emoji }} [{{ $recurrentError->context->sourceName }} → {{ $recurrentError->details->category->type }}] {{ $recurrentError->details->category->title }}</h2>
        <p>Se ha detectado un error repetido en el log de {{ $recurrentError->context->sourceName }}:</p>

        <h4>📝 Mensaje:</h4>
        <pre style="background: #f0f0f0; padding: 10px; border-left: 4px solid #e74c3c;">{{ $recurrentError->details->messageText }}</pre>

        <p><strong>🔁 Ocurrencias:</strong> {{ $recurrentError->count }} veces en los últimos {{ $recurrentError->context->scanWindow }} minutos.</p>
        <p><strong>📅 Fecha:</strong> {{ $recurrentError->context->date }}</p>

        <p><strong>🔎 Revisión rápida:</strong></p>
        <p>
            Puedes acceder al visor de logs aquí:<br>
            <a href="{{  $recurrentError->context->logViewerUrl }}" style="color: #3498db;">{{ $recurrentError->context->logViewerUrl }}</a>
        </p>

        <hr>
        <p style="font-size: 12px; color: #888;">Este mensaje fue generado automáticamente por el sistema de monitoreo de logs.</p>
    </div>
</body>
</html>
