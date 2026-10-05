1.3.13 — Continuous In-Place Navigation
- Adds same-origin in-place navigation for normal site links so persistent UI such as the Nfinite global audio player is not destroyed between pages.
- Updates main content, contextual secondary navigation, title, body classes, canonical/social metadata, history and scroll position.
- Leaves admin, login, WooCommerce transaction flows, downloads and external links as full navigations.
- Dispatches wpnfinite:navigation-complete after each content swap for plugin re-binding.


1.3.8 — Contextual Secondary Navigation Fix
- Restores the secondary navigation changing by page context.
- Music, release, Radio, Beats, Producers, and release-type pages use the dedicated Music rail.
- Adds Radio immediately after New Music in the Music rail.
- All non-music pages continue using the normal Explore / Topics Navigation menu.
- Preserves URLs from an assigned Topics menu when matching Music destinations already exist.
- Does not rewrite or permanently modify the stored WordPress menu.

== 1.3.7 — Living Homepage Visual Correction ==

Corrective release for WPNfinite 1.3.4.

Key changes:
- Adds an explicitly enqueued homepage.css asset. The 1.3.4 Living Homepage rules were placed in style.css, while WPNfinite's asset loader did not enqueue style.css, which caused the new homepage modules to render largely unstyled.
- Preserves the dynamic Community, New Music, Stories, Creators and Events queries introduced in 1.3.4.
- Reduces the homepage Community teaser to two compact conversational cards.
- Locks New Music into a consistent four-column square release grid on desktop, two-column tablet and horizontal mobile rail.
- Keeps Latest Stories and Creators to Know aligned to the established WPNfinite editorial grid.
- Converts Events to compact cards rather than oversized feature blocks.
- Tightens the Explore PairOfDice section into a supporting strip.
- Adds strong scoped overrides for Nfinite Creator Feed cards so homepage presentation stays compact without changing the full /community/ experience.
- Responsive QA targets desktop, tablet and mobile layouts.


1.3.7 — Homepage Content Query Fix
- Restricts hero, Trending Now, and Latest Stories to standard published posts only.
- Top-level Pages such as Home, About, Shop, and service pages can no longer appear as editorial cards.
- Sticky standard posts are preferred for the lead hero when available, with newest posts as fallback.
- Adds a defensive post-type check so third-party query filters cannot leak Pages into homepage editorial modules.


1.3.11
- Adds All Releases to the contextual Music secondary navigation after Radio.
- Recognizes /all-releases/ as a Music navigation context.

== 1.4.0 ==
Nfinite Creators Platform UX integration for the 0.44-0.56 creator-platform updates.
- Adds fan-facing header access to Unified Search, My Library, and Notifications.
- Adds contextual Explore navigation for Discover, Search, Playlists, Organizations, Opportunities, Library, and Notifications.
- Creates missing utility pages without overwriting existing pages: Discover, Search (/find/), My Library, Playlists, Opportunities, and Organizations.
- Adds homepage integration for the home_featured monetization placement and Discovery V2 recommendations/trending.
- Adds presentation styles for Personal Library, Playlists & Collections, Discovery, monetization placements, and playlist controls.
- Preserves Nfinite as the source of truth for ranking, permissions, engagement, earnings, memberships, campaigns, and fan data.
- Preserves WPNfinite persistent navigation/player behavior and re-runs light UI enhancements after wpnfinite:navigation-complete.

== 1.4.1 ==
* Fixes Nfinite Unified Search card text inheriting WPNfinite light/green text colors on white card backgrounds.
* Forces readable dark text and links inside white search result cards while preserving the existing dark site UI.

== 1.4.3 ==
* Fixed contrast on Nfinite Discovery and Personal Library white cards, including homepage Discovery V2 cards.
* Preserves the 1.4.1 Unified Search contrast fix.
