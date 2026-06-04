# Translated readmes (for the WordPress.org directory)

These `readme-{locale}.txt` files are **translations of the directory-facing prose**
(short description, Description, Installation, FAQ) into Japanese, Spanish, German,
French, and Persian.

## How WordPress.org localizes the plugin page
The plugin directory does **not** read bundled `readme-{locale}.txt` files. It localizes
the readme via **https://translate.wordpress.org** (GlotPress). After the plugin is
approved and live, a *"Stable Readme (latest release)"* sub-project is created
automatically; the translated description then appears on locale pages
(e.g. `https://ja.wordpress.org/plugins/reqlock/`).

## How to use these files
After approval, open each language's *Stable Readme* project on translate.wordpress.org
and paste the matching translation from the file here. This is a head-start so the
localized plugin pages go live quickly instead of waiting for community translators.

- The plugin's **UI strings** (`/languages/*.po|.mo`) are already translated and bundled.
- These files cover the **readme prose** only. Header metadata (Contributors, Tags,
  Stable tag, etc.) and the Changelog are intentionally left in English — they are not
  translated on .org.
