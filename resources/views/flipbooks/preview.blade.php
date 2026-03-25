<style>
    * { box-sizing: border-box; }

/* Back Button */
.back-button {
  position: fixed;
  top: 1.5rem;
  left: 1.5rem;
  z-index: 2000;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: none;
  padding: 0.75rem 1.25rem;
  border-radius: 50px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.95rem;
  color: hsl(180 68% 5%);
  text-decoration: none;
  font-weight: 500;
}

.back-button:hover {
  background: rgba(255, 255, 255, 1);
  box-shadow: 0 6px 25px rgba(0, 0, 0, 0.2);
  transform: translateX(-2px);
}

.back-button:active {
  transform: translateX(0);
}

.back-button svg {
  width: 18px;
  height: 18px;
}

@media (max-width: 768px) {
  .back-button {
    top: 1rem;
    left: 1rem;
    padding: 0.6rem 1rem;
    font-size: 0.85rem;
  }
  
  .back-button svg {
    width: 16px;
    height: 16px;
  }
}

/* FlipBook */

body {
  /* or any other parent wrapper */
  margin: 0;
  display: flex;
  min-height: 100dvh;
  perspective: 1000px;
  font: 1em/1.4 "Poppins", sans-serif;
  overflow: hidden;
  color: hsl(180 68% 5%);
  background-color: hsl(187 20% 95%);
  background-image: 
    linear-gradient(rgba(0, 0, 0, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(0, 0, 0, 0.03) 1px, transparent 1px);
  background-size: 50px 50px;
}

.book {
  position: relative;
  display: flex;
  margin: auto;
  width: 40cqmin;
  /*1* let pointer event go trough pages of lower Z than .book */
  pointer-events: none;
  transform-style: preserve-3d;
  transition: translate 1s;
  translate: calc(min(var(--c), 1) * 50%) 0%;
  /* Incline on the X axis for pages preview */
  rotate: 1 0 0 30deg;
}

.page {
  /* PS: Don't go below thickness 0.5 or the pages might transpare */
  --thickness: 4;
  flex: none;
  display: flex;
  width: 100%;
  font-size: 2cqmin;
  /*1* allow pointer events on pages */
  pointer-events: all;
  user-select: none;
  transform-style: preserve-3d;
  transform-origin: left center;
  transition:
    transform 1s,
    rotate 1s ease-in calc((min(var(--i), var(--c)) - max(var(--i), var(--c))) * 50ms);
  translate: calc(var(--i) * -100%) 0px 0px;
  transform: translateZ( calc((var(--c) - var(--i) - 0.5) * calc(var(--thickness) * .23cqmin)));
  rotate: 0 1 0 calc(clamp(0, var(--c) - var(--i), 1) * -180deg);
}

.front,
.back {
  position: relative;
  flex: none;
  width: 100%;
  backface-visibility: hidden;
  overflow: hidden;
  background-color: #fff;
  /* Fix backface visibility Firefox: */
  translate: 0px;
}

.back {
  translate: -100% 0;
  rotate: 0 1 0 180deg;
}


/* That's it. Your FlipBook customization styles: */

.book {
  counter-reset: page -1;
  & a {
    color: inherit;
  }
}

.page {
  box-shadow: 0em .5em 1em -.2em #00000020;
}

.front,
.back {
  display: flex;
  flex-flow: column wrap;
  justify-content: space-between;
  padding: 2em;
  border: 1px solid #0002;
}

.front:has(img),
.back:has(img) {
  padding: 0;
}

.front img,
.back img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: object-fit 0.3s ease, width 0.3s ease, height 0.3s ease;
}

/* Image size modes */
.front img.img-fit-cover,
.back img.img-fit-cover {
  object-fit: cover;
  width: 100%;
  height: 100%;
}

.front img.img-fit-contain,
.back img.img-fit-contain {
  object-fit: contain;
  width: 100%;
  height: 100%;
  background-color: #f5f5f5;
}

.front img.img-fit-fill,
.back img.img-fit-fill {
  object-fit: fill;
  width: 100%;
  height: 100%;
}

.front img.img-fit-width,
.back img.img-fit-width {
  width: 100%;
  height: auto;
  object-fit: contain;
}

.front img.img-fit-height,
.back img.img-fit-height {
  width: auto;
  height: 100%;
  object-fit: contain;
}

  &::after {
    position: absolute;
    bottom: 1em;
    counter-increment: page;
    content: counter(page) ".";
    font-size: 0.8em;
  }
}
.cover {
  &::after {
    content: "";
  }
}
.front {
  &::after {
    right: 1em;
  }
  background: linear-gradient(to left, #f7f7f7 80%, #eee 100%);
  border-radius: .1em .5em .5em .1em;
}
.back {
  &::after {
    left: 1em;
  }
  background-image: linear-gradient(to right, #f7f7f7 80%, #eee 100%);
  border-radius: .5em .1em .1em .5em;
}

.cover {
  background: radial-gradient(circle farthest-corner at 80% 20%, hsl(150 80% 20% / .3) 0%, hsl(170 60% 10% / .1) 100%),
    hsl(231, 32%, 29%) url("https://picsum.photos/id/984/800/900") 50% / cover;
  color: hsl(200 30% 98%);
}

/* Zoom Container */
.zoom-container {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  transition: transform 0.3s ease;
  transform-origin: center center;
}

/* Toolbar Styles */
.flipbook-toolbar {
  position: fixed;
  bottom: 2rem;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 0.5rem;
  align-items: center;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  padding: 0.75rem 1.5rem;
  border-radius: 50px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  z-index: 1000;
  transition: all 0.3s ease;
}

.flipbook-toolbar:hover {
  box-shadow: 0 6px 25px rgba(0, 0, 0, 0.2);
}

.toolbar-btn {
  background: none;
  border: none;
  font-size: 1.2rem;
  color: hsl(180 68% 5%);
  cursor: pointer;
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 40px;
  min-height: 40px;
}

.toolbar-btn:hover {
  background: rgba(0, 0, 0, 0.05);
  transform: scale(1.1);
}

.toolbar-btn:active {
  transform: scale(0.95);
}

.toolbar-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
  transform: none;
}

.toolbar-separator {
  width: 1px;
  height: 30px;
  background: rgba(0, 0, 0, 0.1);
  margin: 0 0.25rem;
}

/* Image Size Dropdown */
.image-size-dropdown {
  position: relative;
  display: inline-block;
}

.image-size-dropdown .dropdown-menu {
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  margin-bottom: 0.5rem;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
  padding: 0.5rem;
  min-width: 200px;
  z-index: 1000;
  border: 1px solid rgba(0, 0, 0, 0.1);
}

.image-size-dropdown .dropdown-item {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  padding: 0.75rem 1rem;
  border: none;
  background: none;
  width: 100%;
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.2s ease;
  text-align: left;
}

.image-size-dropdown .dropdown-item:hover {
  background: rgba(0, 0, 0, 0.05);
}

.image-size-dropdown .dropdown-item span {
  font-size: 0.9rem;
  font-weight: 500;
  color: hsl(180 68% 5%);
  margin-bottom: 0.25rem;
}

.image-size-dropdown .dropdown-item small {
  font-size: 0.75rem;
  color: rgba(0, 0, 0, 0.6);
}

.image-size-dropdown .dropdown-item.active {
  background: rgba(0, 0, 0, 0.1);
}

.image-size-dropdown .dropdown-item.active span {
  font-weight: 600;
}

.zoom-level {
  font-size: 0.9rem;
  color: hsl(180 68% 5%);
  padding: 0 0.5rem;
  min-width: 50px;
  text-align: center;
  font-weight: 500;
}

.page-info {
  font-size: 0.9rem;
  color: hsl(180 68% 5%);
  padding: 0 0.75rem;
  font-weight: 500;
  white-space: nowrap;
}

.page-jump-input {
  width: 60px;
  padding: 0.25rem 0.5rem;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 6px;
  font-size: 0.85rem;
  text-align: center;
  background: white;
  color: hsl(180 68% 5%);
}

.page-jump-input:focus {
  outline: none;
  border-color: hsl(180 68% 30%);
  box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05);
}

.toolbar-btn.active {
  background: rgba(0, 0, 0, 0.1);
}

.toolbar-group {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

/* Thumbnails Panel */
.thumbnails-panel {
  position: fixed;
  right: -400px;
  top: 50%;
  transform: translateY(-50%);
  width: 380px;
  max-height: 80vh;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
  z-index: 999;
  transition: right 0.3s ease;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.thumbnails-panel.show {
  right: 1rem;
}

.thumbnails-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.thumbnails-header h4 {
  margin: 0;
  font-size: 1rem;
  color: hsl(180 68% 5%);
}

.thumbnails-grid {
  padding: 1rem;
  overflow-y: auto;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
  gap: 0.75rem;
}

.thumbnail-item {
  position: relative;
  aspect-ratio: 3/4;
  border-radius: 6px;
  overflow: hidden;
  cursor: pointer;
  border: 2px solid transparent;
  transition: all 0.2s ease;
  background: #f5f5f5;
}

.thumbnail-item:hover {
  border-color: hsl(180 68% 30%);
  transform: scale(1.05);
}

.thumbnail-item.active {
  border-color: hsl(180 68% 50%);
  box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1);
}

.thumbnail-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.thumbnail-number {
  position: absolute;
  bottom: 4px;
  right: 4px;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  font-size: 0.7rem;
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: 500;
}

@media (max-width: 768px) {
  .thumbnails-panel {
    width: 300px;
    right: -320px;
  }
  
  .thumbnails-panel.show {
    right: 0.5rem;
  }
  
  .thumbnails-grid {
    grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
    gap: 0.5rem;
  }
  
  .page-jump-input {
    width: 50px;
    font-size: 0.8rem;
  }
}

@media (max-width: 768px) {
  .flipbook-toolbar {
    bottom: 1rem;
    padding: 0.5rem 1rem;
    gap: 0.25rem;
  }
  
  .toolbar-btn {
    font-size: 1rem;
    padding: 0.4rem 0.6rem;
    min-width: 36px;
    min-height: 36px;
  }
  
  .zoom-level {
    font-size: 0.8rem;
    min-width: 40px;
  }
}

</style>

<!-- Back Button -->
@if(isset($publicUrl))
    <a href="{{ route('flipbooks.show', $flipBook->id) }}" class="back-button">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
        <span>Back to Flip Book</span>
    </a>
@else
    <button onclick="window.history.back()" class="back-button">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
        <span>Back</span>
    </button>
@endif

<div class="zoom-container" id="zoomContainer">
  <div class="book" id="flipbook">
    @if($flipBook->pages && count($flipBook->pages) > 0)
        @php
            $totalPages = count($flipBook->pages);
            $pagePairs = [];
            // Create page pairs: each physical page shows 2 logical pages
            for ($i = 0; $i < $totalPages; $i += 2) {
                $pagePairs[] = [
                    'front' => $flipBook->pages[$i] ?? null,
                    'back' => ($i + 1 < $totalPages) ? $flipBook->pages[$i + 1] : null,
                    'frontIndex' => $i,
                    'backIndex' => $i + 1
                ];
            }
        @endphp
        @foreach($pagePairs as $pairIndex => $pair)
            <div class="page">
                <div class="front {{ $pairIndex === 0 ? 'cover' : '' }}">
                    @if($pair['front'])
                        <img src="{{ asset('storage/' . $pair['front']['path']) }}" alt="Page {{ $pair['frontIndex'] + 1 }}" loading="lazy">
                    @endif
                </div>
                <div class="back {{ $pairIndex === count($pagePairs) - 1 && !$pair['back'] ? 'cover' : '' }}">
                    @if($pair['back'])
                        <img src="{{ asset('storage/' . $pair['back']['path']) }}" alt="Page {{ $pair['backIndex'] + 1 }}" loading="lazy">
                    @elseif($pairIndex === count($pagePairs) - 1)
                        <div style="display: flex; align-items: center; justify-content: center; height: 100%; padding: 2em;">
                            <p style="color: hsl(200 30% 98%);">End of Book</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="page">
            <div class="front cover">
                <div style="display: flex; align-items: center; justify-content: center; height: 100%; padding: 2em;">
                    <p>No pages available</p>
                </div>
            </div>
            <div class="back cover">
                <div style="display: flex; align-items: center; justify-content: center; height: 100%; padding: 2em;">
                    <p>No pages available</p>
                </div>
            </div>
        </div>
    @endif
  </div>
</div>

<!-- Flipbook Toolbar -->
<div class="flipbook-toolbar">
    <!-- Navigation Group -->
    <div class="toolbar-group">
        <button class="toolbar-btn" id="prevPageBtn" onclick="previousPage()" title="Previous Page">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
        </button>
        <div class="page-info" id="pageInfo">1 / {{ count($flipBook->pages ?? []) }}</div>
        <button class="toolbar-btn" id="nextPageBtn" onclick="nextPage()" title="Next Page">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>
    </div>
    <div class="toolbar-separator"></div>
    
    <!-- Page Jump -->
    <div class="toolbar-group">
        <input type="number" class="page-jump-input" id="pageJumpInput" min="1" max="{{ count($flipBook->pages ?? []) }}" value="1" onkeypress="if(event.key==='Enter') jumpToPage()" title="Jump to page">
        <button class="toolbar-btn" onclick="jumpToPage()" title="Go to Page">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </button>
    </div>
    <div class="toolbar-separator"></div>
    
    <!-- Zoom Group -->
    <div class="toolbar-group">
        <button class="toolbar-btn" id="zoomOutBtn" onclick="zoomOut()" title="Zoom Out">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="8" y1="11" x2="14" y2="11"></line>
            </svg>
        </button>
        <div class="zoom-level" id="zoomLevel">100%</div>
        <button class="toolbar-btn" id="zoomInBtn" onclick="zoomIn()" title="Zoom In">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="11" y1="8" x2="11" y2="14"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
            </svg>
        </button>
        <button class="toolbar-btn" id="zoomResetBtn" onclick="zoomReset()" title="Reset Zoom">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                <path d="M21 3v5h-5"></path>
                <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                <path d="M3 21v-5h5"></path>
            </svg>
        </button>
    </div>
    <div class="toolbar-separator"></div>
    
    <!-- Image Size Adjustment -->
    <div class="toolbar-group">
        <div class="image-size-dropdown">
            <button class="toolbar-btn" id="imageSizeBtn" onclick="toggleImageSizeMenu()" title="Adjust Image Size">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <rect x="7" y="7" width="10" height="10"></rect>
                </svg>
                <span id="imageSizeLabel" style="font-size: 0.75rem; margin-left: 4px;">Cover</span>
            </button>
            <div class="dropdown-menu" id="imageSizeMenu" style="display: none;">
                <button class="dropdown-item" onclick="setImageSize('cover')" data-size="cover">
                    <span>Cover</span>
                    <small>Fill entire page, may crop</small>
                </button>
                <button class="dropdown-item" onclick="setImageSize('contain')" data-size="contain">
                    <span>Contain</span>
                    <small>Show full image, may have borders</small>
                </button>
                <button class="dropdown-item" onclick="setImageSize('fill')" data-size="fill">
                    <span>Fill</span>
                    <small>Stretch to fill, may distort</small>
                </button>
                <button class="dropdown-item" onclick="setImageSize('width')" data-size="width">
                    <span>Fit Width</span>
                    <small>Fit to page width</small>
                </button>
                <button class="dropdown-item" onclick="setImageSize('height')" data-size="height">
                    <span>Fit Height</span>
                    <small>Fit to page height</small>
                </button>
            </div>
        </div>
    </div>
    <div class="toolbar-separator"></div>
    
    <!-- View Controls -->
    <div class="toolbar-group">
        <button class="toolbar-btn" id="thumbnailsBtn" onclick="toggleThumbnails()" title="Thumbnails">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
        </button>
        <button class="toolbar-btn" id="fullscreenBtn" onclick="toggleFullscreen()" title="Fullscreen">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
            </svg>
        </button>
    </div>
    <div class="toolbar-separator"></div>
    
    <!-- Actions -->
    <div class="toolbar-group">
        <button class="toolbar-btn" id="autoplayBtn" onclick="toggleAutoplay()" title="Autoplay">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="5 3 19 12 5 21 5 3"></polygon>
            </svg>
        </button>
        <button class="toolbar-btn" onclick="printFlipbook()" title="Print">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
        </button>
        <button class="toolbar-btn" onclick="downloadFlipbook()" title="Download">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
        </button>
    </div>
</div>

<!-- Thumbnails Panel -->
<div class="thumbnails-panel" id="thumbnailsPanel">
    <div class="thumbnails-header">
        <h4>Pages</h4>
        <button class="toolbar-btn" onclick="toggleThumbnails()" title="Close">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
    <div class="thumbnails-grid">
        @if($flipBook->pages && count($flipBook->pages) > 0)
            @foreach($flipBook->pages as $index => $page)
                <div class="thumbnail-item" data-page="{{ $index + 1 }}" onclick="goToPage({{ $index + 1 }})">
                    <img src="{{ asset('storage/' . $page['path']) }}" alt="Page {{ $index + 1 }}" loading="lazy">
                    <span class="thumbnail-number">{{ $index + 1 }}</span>
                </div>
            @endforeach
        @endif
    </div>
</div>

  <script>

const flipBook = (elBook) => {
  elBook.style.setProperty("--c", 0); // Set current page
  elBook.querySelectorAll(".page").forEach((page, idx) => {
    page.style.setProperty("--i", idx);
    page.addEventListener("click", (evt) => {
      if (evt.target.closest("a")) return;
      const curr = evt.target.closest(".back") ? idx : idx + 1;
      elBook.style.setProperty("--c", curr);
    });
  });
};

// Zoom functionality
let currentZoom = 1;
const minZoom = 0.5;
const maxZoom = 3;
const zoomStep = 0.25;

function updateZoom() {
  const zoomContainer = document.getElementById('zoomContainer');
  if (zoomContainer) {
    // Apply scale transform to the zoom container
    zoomContainer.style.transform = `scale(${currentZoom})`;
    
    // Update zoom level display
    const zoomLevel = document.getElementById('zoomLevel');
    if (zoomLevel) {
      zoomLevel.textContent = Math.round(currentZoom * 100) + '%';
    }
    
    // Update button states
    const zoomInBtn = document.getElementById('zoomInBtn');
    const zoomOutBtn = document.getElementById('zoomOutBtn');
    
    if (zoomInBtn) {
      zoomInBtn.disabled = currentZoom >= maxZoom;
    }
    if (zoomOutBtn) {
      zoomOutBtn.disabled = currentZoom <= minZoom;
    }
  }
}

function zoomIn() {
  if (currentZoom < maxZoom) {
    currentZoom = Math.min(currentZoom + zoomStep, maxZoom);
    updateZoom();
  }
}

function zoomOut() {
  if (currentZoom > minZoom) {
    currentZoom = Math.max(currentZoom - zoomStep, minZoom);
    updateZoom();
  }
}

function zoomReset() {
  currentZoom = 1;
  updateZoom();
}

// Image Size Adjustment
let currentImageSize = localStorage.getItem('flipbookImageSize') || 'cover';

function toggleImageSizeMenu() {
  const menu = document.getElementById('imageSizeMenu');
  if (menu) {
    const isVisible = menu.style.display !== 'none';
    menu.style.display = isVisible ? 'none' : 'block';
    
    // Close menu when clicking outside
    if (!isVisible) {
      setTimeout(() => {
        document.addEventListener('click', closeImageSizeMenu);
      }, 0);
    } else {
      document.removeEventListener('click', closeImageSizeMenu);
    }
  }
}

function closeImageSizeMenu(event) {
  const menu = document.getElementById('imageSizeMenu');
  const btn = document.getElementById('imageSizeBtn');
  
  if (menu && btn && event && !menu.contains(event.target) && !btn.contains(event.target)) {
    menu.style.display = 'none';
    document.removeEventListener('click', closeImageSizeMenu);
  }
}

function setImageSize(size) {
  currentImageSize = size;
  localStorage.setItem('flipbookImageSize', size);
  
  // Apply to all images in the flipbook
  const images = document.querySelectorAll('.front img, .back img');
  images.forEach(img => {
    // Remove all size classes
    img.classList.remove('img-fit-cover', 'img-fit-contain', 'img-fit-fill', 'img-fit-width', 'img-fit-height');
    // Add new size class
    img.classList.add(`img-fit-${size}`);
  });
  
  // Update label
  const label = document.getElementById('imageSizeLabel');
  if (label) {
    const labels = {
      'cover': 'Cover',
      'contain': 'Contain',
      'fill': 'Fill',
      'width': 'Fit Width',
      'height': 'Fit Height'
    };
    label.textContent = labels[size] || 'Cover';
  }
  
  // Close menu
  const menu = document.getElementById('imageSizeMenu');
  if (menu) {
    menu.style.display = 'none';
    document.removeEventListener('click', closeImageSizeMenu);
  }
  
  // Update active state in dropdown
  document.querySelectorAll('.dropdown-item').forEach(item => {
    if (item.getAttribute('data-size') === size) {
      item.classList.add('active');
    } else {
      item.classList.remove('active');
    }
  });
}

// Initialize image size on page load
function initImageSize() {
  setImageSize(currentImageSize);
}

// Page navigation
let currentPageIndex = 0;
const totalPages = {{ count($flipBook->pages ?? []) }};
const totalPhysicalPages = Math.ceil(totalPages / 2);

function updatePageInfo() {
  const book = document.getElementById('flipbook');
  const currentPage = parseInt(book.style.getPropertyValue('--c') || 0);
  const logicalPage = (currentPage * 2) + 1;
  const displayPage = Math.min(logicalPage, totalPages);
  
  const pageInfo = document.getElementById('pageInfo');
  if (pageInfo) {
    pageInfo.textContent = `${displayPage} / ${totalPages}`;
  }
  
  const pageJumpInput = document.getElementById('pageJumpInput');
  if (pageJumpInput) {
    pageJumpInput.value = displayPage;
  }
  
  // Update navigation buttons
  const prevBtn = document.getElementById('prevPageBtn');
  const nextBtn = document.getElementById('nextPageBtn');
  if (prevBtn) prevBtn.disabled = currentPage === 0;
  if (nextBtn) nextBtn.disabled = currentPage >= totalPhysicalPages - 1;
  
  // Update active thumbnail
  document.querySelectorAll('.thumbnail-item').forEach((thumb) => {
    const thumbPage = parseInt(thumb.getAttribute('data-page'));
    if (thumbPage === displayPage) {
      thumb.classList.add('active');
    } else {
      thumb.classList.remove('active');
    }
  });
}

function nextPage() {
  const book = document.getElementById('flipbook');
  if (book) {
    const currentPage = parseInt(book.style.getPropertyValue('--c') || 0);
    if (currentPage < totalPhysicalPages - 1) {
      book.style.setProperty('--c', currentPage + 1);
      updatePageInfo();
    }
  }
}

function previousPage() {
  const book = document.getElementById('flipbook');
  if (book) {
    const currentPage = parseInt(book.style.getPropertyValue('--c') || 0);
    if (currentPage > 0) {
      book.style.setProperty('--c', currentPage - 1);
      updatePageInfo();
    }
  }
}

function goToPage(pageNum) {
  if (pageNum >= 1 && pageNum <= totalPages) {
    const book = document.getElementById('flipbook');
    if (book) {
      // Convert logical page to physical page (0-based)
      const physicalPage = Math.floor((pageNum - 1) / 2);
      book.style.setProperty('--c', physicalPage);
      updatePageInfo();
    }
  }
}

function jumpToPage() {
  const pageJumpInput = document.getElementById('pageJumpInput');
  if (pageJumpInput) {
    const pageNum = parseInt(pageJumpInput.value);
    if (pageNum >= 1 && pageNum <= totalPages) {
      goToPage(pageNum);
    } else {
      alert(`Please enter a page number between 1 and ${totalPages}`);
      updatePageInfo();
    }
  }
}

// Thumbnails
function toggleThumbnails() {
  const panel = document.getElementById('thumbnailsPanel');
  const btn = document.getElementById('thumbnailsBtn');
  if (panel) {
    panel.classList.toggle('show');
    if (btn) {
      btn.classList.toggle('active');
    }
  }
}

// Fullscreen
function toggleFullscreen() {
  const btn = document.getElementById('fullscreenBtn');
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen().then(() => {
      if (btn) {
        btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"></path></svg>';
      }
    });
  } else {
    document.exitFullscreen().then(() => {
      if (btn) {
        btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path></svg>';
      }
    });
  }
}

// Autoplay
let autoplayTimer = null;
let isAutoplaying = false;

function toggleAutoplay() {
  if (isAutoplaying) {
    stopAutoplay();
  } else {
    startAutoplay();
  }
}

function startAutoplay() {
  isAutoplaying = true;
  const btn = document.getElementById('autoplayBtn');
  if (btn) {
    btn.classList.add('active');
    btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>';
  }
  
  autoplayTimer = setInterval(() => {
    const book = document.getElementById('flipbook');
    if (book) {
      const currentPage = parseInt(book.style.getPropertyValue('--c') || 0);
      if (currentPage < totalPhysicalPages - 1) {
        nextPage();
      } else {
        stopAutoplay();
      }
    }
  }, 3000); // 3 seconds per page
}

function stopAutoplay() {
  isAutoplaying = false;
  const btn = document.getElementById('autoplayBtn');
  if (btn) {
    btn.classList.remove('active');
    btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>';
  }
  if (autoplayTimer) {
    clearInterval(autoplayTimer);
    autoplayTimer = null;
  }
}

// Print
function printFlipbook() {
  const book = document.getElementById('flipbook');
  if (!book) return;
  
  const pages = [];
  const pageElements = book.querySelectorAll('.page');
  pageElements.forEach((page) => {
    const frontImg = page.querySelector('.front img');
    const backImg = page.querySelector('.back img');
    if (frontImg) pages.push(frontImg.src);
    if (backImg) pages.push(backImg.src);
  });
  
  const printWindow = window.open('', '_blank');
  let printContent = '<html><head><title>Print Flipbook</title><style>';
  printContent += 'body { margin: 0; padding: 20px; }';
  printContent += 'img { width: 100%; max-width: 8.5in; height: auto; page-break-after: always; display: block; }';
  printContent += '@media print { img { page-break-after: always; } }';
  printContent += '</style></head><body>';
  
  pages.forEach((src) => {
    printContent += '<img src="' + src + '">';
  });
  
  printContent += '</body></html>';
  printWindow.document.write(printContent);
  printWindow.document.close();
  
  setTimeout(() => {
    printWindow.print();
  }, 500);
}

// Download
function downloadFlipbook() {
  const book = document.getElementById('flipbook');
  if (!book) return;
  
  const currentPage = parseInt(book.style.getPropertyValue('--c') || 0);
  const pageElements = book.querySelectorAll('.page');
  if (pageElements[currentPage]) {
    const frontImg = pageElements[currentPage].querySelector('.front img');
    if (frontImg) {
      const link = document.createElement('a');
      link.href = frontImg.src;
      link.download = 'flipbook-page-' + (currentPage + 1) + '.jpg';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  }
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
  initImageSize();
  updateZoom();
  
  // Initialize flipbook
  const book = document.getElementById('flipbook');
  if (book) {
    flipBook(book);
    
    // Watch for page changes
    const observer = new MutationObserver(() => {
      updatePageInfo();
    });
    
    observer.observe(book, {
      attributes: true,
      attributeFilter: ['style']
    });
    
    // Also update on click
    book.querySelectorAll(".page").forEach((page) => {
      page.addEventListener("click", () => {
        setTimeout(updatePageInfo, 100);
      });
    });
    
    updatePageInfo();
  }
  
  // Keyboard shortcuts
  document.addEventListener('keydown', function(e) {
    // Arrow keys for navigation
    if (e.key === 'ArrowLeft') {
      e.preventDefault();
      previousPage();
    }
    if (e.key === 'ArrowRight') {
      e.preventDefault();
      nextPage();
    }
    
    // Ctrl/Cmd + Plus for zoom in
    if ((e.ctrlKey || e.metaKey) && (e.key === '+' || e.key === '=')) {
      e.preventDefault();
      zoomIn();
    }
    // Ctrl/Cmd + Minus for zoom out
    if ((e.ctrlKey || e.metaKey) && e.key === '-') {
      e.preventDefault();
      zoomOut();
    }
    // Ctrl/Cmd + 0 for reset zoom
    if ((e.ctrlKey || e.metaKey) && e.key === '0') {
      e.preventDefault();
      zoomReset();
    }
    // Escape to exit fullscreen
    if (e.key === 'Escape' && document.fullscreenElement) {
      toggleFullscreen();
    }
  });
});

  </script>
