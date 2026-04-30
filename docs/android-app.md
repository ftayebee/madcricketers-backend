# MadCricketers Android App

## Overview

The Android app lives in `android/` and is a native Kotlin + Jetpack Compose project. It uses the Laravel backend as the source of truth for authentication, permissions, teams, matches, and official scoreboard calculations.

The app sends score actions to the backend. It does not calculate official cricket scoring locally.

## Setup

1. Start the Laravel backend:
   ```bash
   php artisan serve
   ```
2. Open `android/` in Android Studio.
3. For the Android emulator, the default API base URL is:
   ```text
   http://10.0.2.2:8000/
   ```
4. For a physical device, update `BASE_URL` in `android/app/build.gradle` to your computer LAN IP, for example:
   ```gradle
   buildConfigField "String", "BASE_URL", "\"http://192.168.1.10:8000/\""
   ```
5. Build and run the `app` module.

## Auth Flow

The app uses Laravel Sanctum personal access tokens.

Endpoints:

- `POST /api/login`
- `GET /api/me`
- `POST /api/logout`

The Android app stores the bearer token in Jetpack DataStore and sends it through an OkHttp interceptor:

```text
Authorization: Bearer <token>
```

If the backend returns `401`, the app shows a session-expired message and should route the user back to login.

## Protected API Endpoints

Teams:

- `GET /api/teams`
- `POST /api/teams`
- `GET /api/teams/{id}`
- `PUT /api/teams/{id}`
- `DELETE /api/teams/{id}`
- `POST /api/teams/{id}/players`
- `DELETE /api/teams/{id}/players/{playerId}`

Matches:

- `GET /api/matches`
- `POST /api/matches`
- `GET /api/matches/{id}`
- `PUT /api/matches/{id}`
- `POST /api/matches/{id}/start`
- `POST /api/matches/{id}/toss`

Scoreboard:

- `GET /api/matches/{id}/scoreboard`
- `POST /api/matches/{id}/score`
- `POST /api/matches/{id}/wicket`
- `POST /api/matches/{id}/extras`
- `POST /api/matches/{id}/undo`
- `POST /api/matches/{id}/end-innings`
- `POST /api/matches/{id}/complete`
- `POST /api/matches/{id}/select-batsman`
- `POST /api/matches/{id}/select-bowler`

Players:

- `GET /api/players`
- `GET /api/players?team_id={id}`
- `GET /api/players/search?q={name}`

## Permission Model

The mobile API uses the same Spatie permissions as the admin web panel.

Important permissions:

- Teams: `teams-view`, `teams-create`, `teams-edit`, `teams-delete`
- Matches: `cricket-matches-view`, `cricket-matches-create`, `cricket-matches-edit`, `cricket-matches-start`, `cricket-matches-toss`, `cricket-matches-scoreboard`
- Scoreboard: `scoreboard-view`, `scoreboard-edit`

Super-admin and admin bypass still comes from the existing Laravel `Gate::before`.

## Scoreboard Flow

1. Create or select a match.
2. Start the match with `POST /api/matches/{id}/start`.
3. Store toss with `POST /api/matches/{id}/toss`.
4. Open `GET /api/matches/{id}/scoreboard`.
5. Select striker, non-striker, and bowler.
6. Send deliveries:
   - Normal runs: `POST /api/matches/{id}/score`
   - Extras: `POST /api/matches/{id}/extras`
   - Wickets: `POST /api/matches/{id}/wicket`
7. Poll scoreboard every 5 seconds while the live scoreboard screen is open.
8. End innings or complete match through the protected scoreboard endpoints.

## Known Limitations

- The current Android UI is a phase-one operational scaffold. Team/match create forms are wired at repository/API level, but the first screen pass focuses on login, dashboard, lists, and live scoring.
- Token storage currently uses Jetpack DataStore. For production hardening, add AndroidX Security Crypto or hardware-backed encryption around the token store.
- Real-time Android updates use safe polling. The Laravel backend has Pusher available in Composer, but the default broadcast driver is `null`.
- Some backend scoring edge cases still live in the existing admin scoring controller. The API delegates to that controller to avoid duplicate scoring rules.
