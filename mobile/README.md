# BluePeak Fintech — Mobile App

A [Capacitor](https://capacitorjs.com) WebView wrapper around the production BluePeak Fintech
site. There is no separate mobile codebase to maintain — the app just loads
`https://bluepeakfintech.com/login` (see `capacitor.config.json` → `server.url`) inside a
native shell with a splash screen, app icon, and push notification support. Every screen a
customer sees is the same Blade-rendered site as the browser version; **customer-only** — the
app's entry point (`/login`) is the dedicated Customer login screen and has no Admin/Shop Owner
login path (those live at separate URLs, `/admin` and `/shop-owner/login`, that the app never
loads).

This was scaffolded from this Linux dev machine, which can generate both native projects and
all icon/splash assets, but **cannot compile either app** — there's no Android SDK and no Xcode
here (Xcode only runs on macOS). Building the installable APK/IPA needs the steps below, on a
machine (or CI) that has the right toolchain.

## Quickest way to get an APK: GitHub Actions

`.github/workflows/build-mobile.yml` builds both apps on GitHub's own runners (which already
have the Android SDK and, for iOS, Xcode) — no local setup needed. Go to **Actions → Build
Mobile App → Run workflow**, or just push a change under `mobile/`. When it finishes:
- **Android**: download the `bluepeak-fintech-debug-apk` artifact from the run — a real,
  installable `.apk` you can sideload onto any Android phone (enable "Install from unknown
  sources" for the browser/file app you use to open it). It's debug-signed, fine for testing,
  not for the Play Store (that needs your own release-signing key).
- **iOS**: the job only builds for the Simulator with code signing disabled, to prove the
  project still compiles — it does not produce anything installable on a real iPhone. A real
  device or App Store build needs your own Apple Developer signing certificate (see "Building
  for iOS" below); nothing here can substitute for that.

## One-time setup

```bash
cd mobile
npm install
```

## Changing the server URL

Edit `server.url` in `capacitor.config.json`, then re-sync:

```bash
npx cap sync
```

It's currently pointed at `https://bluepeakfintech.com/login` — update this if the real
production domain ends up different from what's in `.env.production.example`.

## Icons & splash screen

`assets/icon.png` (1024×1024) and `assets/splash.png` (2732×2732) are the source images
`@capacitor/assets` generated every platform-specific resolution from. They were composited by
`assets/generate-sources.php` from the existing `public/images/logo-icon.png` — which is only
484×495, so the generated icon is upscaled and will look a little soft at large sizes (e.g. the
Play Store listing image, iOS App Store icon). **Before publishing**, drop a real 1024×1024
`assets/icon.png` in (and optionally a nicer `assets/splash.png`) and regenerate:

```bash
npm run assets
npx cap sync
```

## Biometric login (Face ID / Touch ID / Android fingerprint)

Two separate mechanisms cover this, depending on where the customer is:

- **Browser (Chrome/Safari)** — real WebAuthn passkeys, no mobile-specific setup needed, works
  today. See `resources/views/partials/passkeys.blade.php` in the main Laravel app.
- **Inside this app** — `@capgo/capacitor-native-biometric`, calling the real native OS prompt
  (Android `BiometricPrompt` / iOS `LocalAuthentication`) directly, via
  `resources/views/partials/native-biometric.blade.php`.

These used to be the same WebAuthn mechanism everywhere, gated behind Digital Asset Links
(`.well-known/assetlinks.json` / `apple-app-site-association`, `ANDROID_SHA256_FINGERPRINTS` in
the server's `.env`) so the app's embedded WebView could touch platform credentials. In practice
that turned out to be unreliable across real Android devices — some WebView builds just don't
support the in-WebView WebAuthn bridge at all, asset-link verification can get stuck cached from
before it was configured, etc. — so the app now uses a real native plugin instead, which doesn't
depend on any of that. The `.well-known/*` files and `ANDROID_SHA256_FINGERPRINTS` env var are
still served/read (harmless, and Associated Domains has other legitimate uses), but they're no
longer what makes in-app biometric work.

How it works: after a customer turns it on (Profile → **Enable Biometric Login**), the app asks
the server for a random opaque token (`POST /biometric/enable`) and stores it via the plugin in
OS-level secure storage (Android Keystore / iOS Keychain), protected by `AccessControl.BIOMETRY_ANY`
— retrieving it later requires a live biometric prompt, enforced by the OS itself, not by app
code. On next login (or on the app-lock re-entry screen, see `EnsureAppUnlocked`), the plugin's
`getSecureData()` triggers that prompt, and on success the app posts the retrieved token to
`POST /biometric/login` to actually log in. The server never sees a password or biometric data,
only this one-time-issued token, hashed at rest (`users.biometric_token_hash`) exactly like the
Quick PIN token.

**No `.env` setup needed for this to work** — unlike the old approach, there's no signing-key
fingerprint dependency, so it works the same on a debug build as a release build. If a customer
reports biometric not showing up as an option, check:

1. `Profile → Biometric Login` shows "Biometric login isn't supported on this device" — that
   means `NativeBiometric.isAvailable()` returned false: no biometric hardware, nothing enrolled
   as the device's lock screen method (having the sensor isn't enough — the OS needs a fingerprint/
   face actually registered as a way to unlock the phone), or the OS/Play Services combination is
   too old. This is a genuine device limitation, not fixable from the app.
2. Android needs `<uses-permission android:name="android.permission.USE_BIOMETRIC" />` in
   `AndroidManifest.xml` (already added) and the `AppTheme.Transparent` style the plugin's
   `AuthActivity` uses on older Android versions (already added to `styles.xml`).
3. iOS needs `NSFaceIDUsageDescription` in `Info.plist` (already added) or the app will crash
   when Face ID is attempted, not just fail gracefully.

## OneSignal (push notifications)

Nothing to configure here in the app itself — the app reads the OneSignal App ID from the live
site at runtime (Admin sets it once in **Admin → Notification Manager → Settings**, on the
website, not in this repo). The `onesignal-cordova-plugin` package is already installed and
wired up; `resources/views/partials/onesignal-bridge.blade.php` (in the main Laravel app) calls
`OneSignal.login(userId)` after a customer authenticates and `OneSignal.logout()` when they're
not, so Admin's automated notifications (order placed, loan approved, EMI reminders, ...) and
manual pushes reach the right device.

You do still need to do the native-side OneSignal setup once per platform before push actually
works, following [OneSignal's Capacitor guide](https://documentation.onesignal.com/docs/capacitor-sdk-setup):
- **Android**: add your Firebase config (`google-services.json`) to `android/app/`.
- **iOS**: enable Push Notifications + Background Modes capabilities in Xcode, add your Apple
  Push certificate/key in the OneSignal dashboard.

## Building for Android

Needs [Android Studio](https://developer.android.com/studio) (which bundles the Android SDK).

```bash
npx cap open android
```

This opens the `android/` folder in Android Studio. From there: `Build → Generate Signed Bundle
/ APK` for a release build, or just Run for a debug build on an emulator/device. Command-line
alternative once the SDK is installed and `ANDROID_HOME` is set:

```bash
cd android && ./gradlew assembleRelease
```

## Building for iOS

**Needs a Mac with Xcode** — there is no way around this; Apple only allows iOS builds from
macOS. Options:
1. Copy/clone this repo onto a Mac, `cd mobile && npm install && npx cap open ios`, then
   `Product → Archive` in Xcode to build for the App Store.
2. Use a cloud Mac CI (GitHub Actions `macos-latest` runner, [Codemagic](https://codemagic.io),
   or [Ionic Appflow](https://ionic.io/appflow)) if you don't have physical Mac access — all of
   these can build and even submit to TestFlight/App Store from this same `mobile/` folder
   without you owning a Mac.

## Publishing to the Play Store (Android)

`.github/workflows/release-android.yml` builds a signed, upload-ready `.aab` in CI. One-time
setup:

1. A signing keystore was generated for this app (`bluepeak-upload-key.jks`) and handed to you
   directly (not committed here — a signing key must never be in git). **Back it up somewhere
   safe outside this repo** — if it's lost, you can never publish an update to this app under
   the same listing again.
2. In **GitHub → this repo → Settings → Secrets and variables → Actions**, add 4 secrets (the
   exact values were included with the keystore handoff):
   `ANDROID_KEYSTORE_BASE64`, `ANDROID_KEYSTORE_PASSWORD`, `ANDROID_KEY_ALIAS`,
   `ANDROID_KEY_PASSWORD`.
3. Run **Actions → Release Android (signed AAB) → Run workflow**. Download the
   `bluepeak-fintech-release-aab` artifact when it finishes.
4. In [Play Console](https://play.google.com/console) → your app → **Production** (or a testing
   track first) → **Create new release**, upload the `.aab`. First-ever upload also asks you to
   opt into **Play App Signing** — accept it (Google recommended default): Google re-signs the
   app with a key it manages for the actual Play Store distribution, so losing the upload
   keystore later is recoverable (you'd request a new upload key from Google), whereas losing a
   *non*-Play-App-Signing key is unrecoverable.
5. Fill in the store listing (screenshots, description, privacy policy URL, content rating
   questionnaire, data safety form) — none of that is something this repo can generate; it's
   done in the Play Console UI.

Before your first real submission, bump `versionCode`/`versionName` in
`mobile/android/app/build.gradle` for each subsequent release (Play Console rejects re-uploading
the same `versionCode` twice), and swap in a real 1024×1024 icon (see "Icons & splash screen"
above).

## Publishing to the App Store (iOS)

`.github/workflows/release-ios.yml` builds, signs, and uploads to TestFlight on a
`macos-latest` GitHub Actions runner using an **App Store Connect API key** — no Mac needed by
you at any point, no logged-in Apple ID in CI. **This hasn't been run/verified end to end** (the
environment that wrote it has no Mac/Xcode access to test against) — the xcodebuild/export flags
follow Apple's documented CI pattern, but treat the first real run as a shakedown; it may need a
small fix or two (e.g. the App ID needing to be registered in your Apple Developer account
first, or an export-options tweak).

One-time setup:

1. In [App Store Connect](https://appstoreconnect.apple.com) → **Users and Access** →
   **Integrations** → **Keys**, generate a new API key (Admin access). Apple lets you download
   the `.p8` file **exactly once** — save it immediately.
2. In **GitHub → this repo → Settings → Secrets and variables → Actions**, add:
   - `APPLE_TEAM_ID` — from [developer.apple.com](https://developer.apple.com) → Membership
   - `APPSTORE_API_KEY_ID` — shown next to the key you just created
   - `APPSTORE_API_ISSUER_ID` — shown at the top of the same Keys page
   - `APPSTORE_API_KEY_P8` — the full contents of the `.p8` file you downloaded
3. Make sure `com.bluepeakfintech.app` is registered as an App ID in your Apple Developer
   account and has a matching app record created in App Store Connect (first-time-only, done
   once in the Apple/App Store Connect UI — not something this workflow can create for you).
4. Run **Actions → Release iOS (TestFlight) → Run workflow**.

If you'd rather not deal with CI signing at all, the alternative is a physical/rented Mac:
clone this repo, `cd mobile && npm install && npx cap sync ios`, open
`ios/App/App.xcworkspace` in Xcode, sign in under **Xcode → Settings → Accounts**, select your
Team in the target's **Signing & Capabilities** tab, then **Product → Archive** → **Distribute
App**.

Either way, first fill in `APPLE_TEAM_ID` in the *server's* `.env` too (see "Biometric login"
above — same value, different place it's used) and complete the one-time Xcode **Associated
Domains** capability step so passkeys work inside the shipped app.
