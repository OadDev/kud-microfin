# BluePeak Fintech — Mobile App

A [Capacitor](https://capacitorjs.com) WebView wrapper around the production BluePeak Fintech
site. There is no separate mobile codebase to maintain — the app just loads
`https://bluepeakfintech.com/app/login` (see `capacitor.config.json` → `server.url`) inside a
native shell with a splash screen, app icon, and push notification support. Every screen a
customer sees is the same Blade-rendered site as the browser version; **customer-only** — the
app's entry point (`/app/login`) has no Admin/Shop Owner login path.

This was scaffolded from this Linux dev machine, which can generate both native projects and
all icon/splash assets, but **cannot compile either app** — there's no Android SDK and no Xcode
here (Xcode only runs on macOS). Building the installable APK/IPA needs the steps below, on a
machine (or CI) that has the right toolchain.

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

It's currently pointed at `https://bluepeakfintech.com/app/login` — update this if the real
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

## Publishing

- **Play Store**: needs a signed `.aab`, a Google Play Console developer account ($25 one-time),
  and store listing assets (screenshots, feature graphic — not generated here).
- **App Store**: needs an Apple Developer Program account ($99/year), a signed build via Xcode
  or CI, and App Store Connect listing assets.

Neither store account/listing is something this repo can set up for you — both require your own
developer accounts and manual review submission.
