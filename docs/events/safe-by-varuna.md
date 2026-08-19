# Safe by Varuna 1 edition: announcement boundary

## Verified source facts

The sources for this draft are the local archive `WhatsApp Chat - Safe by varuna 1 edition.zip` and the event artwork `WhatsApp Image 2026-08-19 at 12.56.54 AM.jpeg`, both supplied by the user. They verify:

- Venue capacity: 350 pax.
- Dress code: black/negro.
- Campaign targeting areas: Condesa, Roma and Polanco.
- The attached venue walkthrough media is real venue reference material.
- Date: August 27, 2026.
- Artist: KAPI; genre: minimal house; “Tulum to CDMX”.
- Venue: Casa Luma Cultural Space.
- Announced address: Tonalá 145, CDMX.
- Limited capacity.

The supplied sources do **not** verify the exact time, price, sale phases, age rule, responsible tenant, payment provider or live ticket URL. Those remain unset.

## Command

```sh
php artisan events:register-safe-by-varuna-draft
```

The default is a read-only preview. It does not create or update a database row.

```sh
php artisan events:register-safe-by-varuna-draft --write-draft
```

This writes exactly one record for `safe-by-varuna-1-edition`. Because the existing `events` schema has no primary-site draft/publication state, the command soft-deletes the record after creation and only updates that soft-deleted record later. Existing public Event queries therefore cannot list it. If a row with this slug is active, the command refuses to overwrite or unpublish it.

The draft records the confirmed venue, city, KAPI lineup text and supplied event artwork. `starts_at` remains unset because the schema requires a datetime and no exact time was provided. It has no `location_id`, `ticket_url`, source/details URL, feature flag, public-site visibility, or ticket products.

`--publish` and `--sell` are explicit refusal switches in this draft-only command. They do not make provider calls, create payment/ticket records, or modify public state. A separate reviewed activation flow would need the exact time, positive price, age rule, a real HTTPS ticket URL, tenant assignment and explicit publication authority before it may alter any public or selling state.

`--publish-announcement --confirm=PUBLISH` restores or creates the exact public announcement with a date-only noon anchor and a `time_tba` tag. Public formatters omit the clock for that tag. The announcement remains non-selling: it creates no ticket product, payment URL or provider call, and the event page captures interest through the existing consented newsletter flow.

`--unpublish-announcement --confirm=UNPUBLISH` soft-deletes only this exact slug when it has no ticket URL or ticket products. It refuses to remove a selling event.

The supplied event artwork is stored as `public/images/events/safe-by-varuna/event-poster.jpg` and is used by the announcement page, event listing and social preview metadata.
