<p>Bonjour {{ $owner_name ?? 'Propriétaire' }},</p>

<p>Vous avez reçu une nouvelle réservation :</p>

<ul>
    <li>Bien : {{ $bienTitle }}</li>
    <li>Client : {{ $clientName }}</li>
    <li>Email : {{ $clientEmail }}</li>
    <li>Téléphone : {{ $clientPhone }}</li>
    <li>Message : {{ $clientMessage }}</li>
    <li>Dates :</li>
    <ul>
        <li>Début : {{ $startDate }}</li>
        <li>Fin : {{ $endDate }}</li>
        <li>Date de visite : {{ $visitDate }}</li>
    </ul>
</ul>

<p>Cordialement,<br>Africa Location</p>
