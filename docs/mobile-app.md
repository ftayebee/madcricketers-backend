# MadCricketers Flutter Mobile App

## Location

The Flutter app is intentionally separate from the Laravel backend:

```text
H:\XAMPP\htdocs\00-PROJECTS\madcricketers-mobile
```

The Laravel backend remains in:

```text
H:\XAMPP\htdocs\00-PROJECTS\madcricketers-backend
```

## Tech Stack

- Flutter
- Dart
- Riverpod
- Dio
- GoRouter
- Flutter Secure Storage
- Material 3

## API Configuration

The app uses environment-based API configuration:

```dart
class ApiConfig {
  static const String baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000/api',
  );
}
```

Local emulator:

```bash
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api
```

Production:

```bash
flutter build apk --release --dart-define=API_BASE_URL=https://app.madcricketers.com/api
```

## Auth Flow

The app uses Laravel Sanctum bearer tokens.

Endpoints:

- `POST /api/login`
- `GET /api/me`
- `POST /api/logout`

The token is stored using Flutter Secure Storage and added to every Dio request:

```text
Authorization: Bearer <token>
```

If Laravel returns `401`, the Dio interceptor clears the local token.

## API Endpoints Used

Teams:

- `GET /api/teams`
- `POST /api/teams`
- `GET /api/teams/{id}`
- `PUT /api/teams/{id}`
- `DELETE /api/teams/{id}`
- `POST /api/teams/{id}/players`
- `DELETE /api/teams/{id}/players/{playerId}`

Players:

- `GET /api/players`
- `GET /api/players/search?q=`
- `GET /api/players?team_id=`

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
- `POST /api/matches/{id}/extras`
- `POST /api/matches/{id}/wicket`
- `POST /api/matches/{id}/undo`
- `POST /api/matches/{id}/end-innings`
- `POST /api/matches/{id}/complete`
- `POST /api/matches/{id}/select-batsman`
- `POST /api/matches/{id}/select-bowler`

## Scoreboard Flow

1. Login.
2. Open or create a match.
3. Start the match.
4. Save toss winner and toss decision.
5. Open scoreboard.
6. Select striker, non-striker, and bowler.
7. Send score actions to Laravel.
8. Refresh/poll scoreboard state from Laravel response.

Flutter does not calculate official score, overs, wickets, innings result, match result, or tournament points. Laravel remains the source of truth.

## Current Screens

- Login
- Dashboard
- Team list
- Team details
- Create/edit team
- Assign players
- Match list
- Create match
- Match details
- Start match
- Toss setup
- Live scoreboard

## Verification Notes

Completed:

- `flutter pub get`
- `flutter analyze`

Known local tooling issue:

- `flutter test` timed out in this Windows environment.
- `flutter build apk --debug` timed out in this Windows environment.

The Dart analyzer reports no issues.
