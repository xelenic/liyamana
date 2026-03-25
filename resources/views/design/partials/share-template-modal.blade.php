{{-- Share popup: Copy link, native share, email, X/Twitter, Facebook --}}
<div id="shareTemplateModal" class="modal share-template-modal-root" style="display: none; position: fixed; z-index: 10001; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(15,23,42,0.45); align-items: center; justify-content: center; padding: 1rem;" onclick="if(event.target === this) closeShareTemplateModal();">
    <div class="share-template-modal-inner" style="background: white; width: 100%; max-width: 420px; border-radius: 16px; overflow: hidden; box-shadow: 0 24px 48px rgba(0,0,0,0.18);" onclick="event.stopPropagation();">
        <div style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); padding: 1.25rem 1.5rem; color: white; display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;">
            <div>
                <h3 class="mb-1" style="font-size: 1.15rem; font-weight: 700; margin: 0; line-height: 1.25; max-width: 280px; overflow: hidden; text-overflow: ellipsis;" id="shareTemplateModalTitle">Share template</h3>
                <p class="mb-0 small" style="opacity: 0.92; font-size: 0.8125rem;">Copy the link or share via email and social.</p>
            </div>
            <button type="button" onclick="closeShareTemplateModal()" aria-label="Close" style="background: rgba(255,255,255,0.2); border: none; font-size: 1.35rem; cursor: pointer; color: white; width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0; line-height: 1;">&times;</button>
        </div>
        <div style="padding: 1.25rem 1.5rem 1.5rem;">
            <label class="form-label small text-muted mb-1">Link</label>
            <div class="input-group input-group-sm mb-3">
                <input type="text" class="form-control" id="shareTemplateUrlInput" readonly style="font-size: 0.8125rem;">
                <button type="button" class="btn btn-primary" id="shareTemplateCopyBtn" onclick="copyShareTemplateLink()">
                    <i class="fas fa-copy me-1"></i>Copy
                </button>
            </div>
            <p class="small text-muted mb-3" id="shareTemplatePrivateHint" style="display: none;"><i class="fas fa-lock me-1"></i>Private templates: only you (when signed in) can open this link.</p>
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm text-start d-flex align-items-center gap-2" id="shareTemplateNativeBtn" style="display: none;" onclick="shareTemplateNativeShare()">
                    <i class="fas fa-share-alt w-20 text-center" style="width: 1.25rem;"></i>
                    <span>Share using device…</span>
                </button>
                <a href="#" class="btn btn-outline-secondary btn-sm text-start d-flex align-items-center gap-2" id="shareTemplateEmailBtn" target="_blank" rel="noopener">
                    <i class="fas fa-envelope w-20 text-center" style="width: 1.25rem;"></i>
                    <span>Email</span>
                </a>
                <a href="#" class="btn btn-outline-secondary btn-sm text-start d-flex align-items-center gap-2" id="shareTemplateTwitterBtn" target="_blank" rel="noopener">
                    <i class="fab fa-twitter w-20 text-center" style="width: 1.25rem;"></i>
                    <span>X / Twitter</span>
                </a>
                <a href="#" class="btn btn-outline-secondary btn-sm text-start d-flex align-items-center gap-2" id="shareTemplateFacebookBtn" target="_blank" rel="noopener">
                    <i class="fab fa-facebook w-20 text-center" style="width: 1.25rem;"></i>
                    <span>Facebook</span>
                </a>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
(function() {
    var shareTplUrl = '';
    var shareTplTitle = '';
    var shareTplIsPublic = true;

    window.getDesignTemplateShareUrl = function(templateId) {
        return @json(rtrim(url('/design/templates'), '/')) + '/' + encodeURIComponent(templateId);
    };

    window.openShareTemplateModal = function(templateId, templateName, isPublic) {
        shareTplTitle = templateName || 'Template';
        shareTplIsPublic = !!isPublic;
        shareTplUrl = window.getDesignTemplateShareUrl(templateId);
        var modal = document.getElementById('shareTemplateModal');
        var input = document.getElementById('shareTemplateUrlInput');
        var titleEl = document.getElementById('shareTemplateModalTitle');
        var hint = document.getElementById('shareTemplatePrivateHint');
        var nativeBtn = document.getElementById('shareTemplateNativeBtn');
        if (!modal || !input) return;
        input.value = shareTplUrl;
        if (titleEl) titleEl.textContent = 'Share: ' + shareTplTitle;
        if (hint) hint.style.display = shareTplIsPublic ? 'none' : 'block';
        if (nativeBtn) {
            nativeBtn.style.display = (navigator.share) ? 'flex' : 'none';
        }
        var encUrl = encodeURIComponent(shareTplUrl);
        var encText = encodeURIComponent('Check out this template: ' + shareTplTitle);
        var body = encodeURIComponent(shareTplTitle + '\n\n' + shareTplUrl);
        var emailBtn = document.getElementById('shareTemplateEmailBtn');
        var twBtn = document.getElementById('shareTemplateTwitterBtn');
        var fbBtn = document.getElementById('shareTemplateFacebookBtn');
        if (emailBtn) emailBtn.href = 'mailto:?subject=' + encodeURIComponent(shareTplTitle) + '&body=' + body;
        if (twBtn) twBtn.href = 'https://twitter.com/intent/tweet?text=' + encText + '&url=' + encUrl;
        if (fbBtn) fbBtn.href = 'https://www.facebook.com/sharer/sharer.php?u=' + encUrl;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    window.closeShareTemplateModal = function() {
        var modal = document.getElementById('shareTemplateModal');
        if (modal) modal.style.display = 'none';
        document.body.style.overflow = '';
    };

    window.copyShareTemplateLink = function() {
        var input = document.getElementById('shareTemplateUrlInput');
        var url = (input && input.value) ? input.value : shareTplUrl;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function() {
                var btn = document.getElementById('shareTemplateCopyBtn');
                if (btn) { var t = btn.innerHTML; btn.innerHTML = '<i class="fas fa-check me-1"></i>Copied'; setTimeout(function() { btn.innerHTML = t; }, 2000); }
            }).catch(function() { fallbackCopyShareUrl(url); });
        } else {
            fallbackCopyShareUrl(url);
        }
    };

    function fallbackCopyShareUrl(url) {
        var el = document.createElement('input');
        el.value = url;
        document.body.appendChild(el);
        el.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(el);
        alert('Link copied to clipboard.');
    }

    window.shareTemplateNativeShare = function() {
        if (!navigator.share || !shareTplUrl) return;
        navigator.share({ title: shareTplTitle, text: 'Check out this template: ' + shareTplTitle, url: shareTplUrl }).catch(function() {});
    };
})();
</script>
@endpush
@endonce
