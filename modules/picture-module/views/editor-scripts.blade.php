<script>
/**
 * Picture Gallery module - adds image from gallery to design canvas.
 * Requires: canvas, addImageToCanvas (or similar) from the design editor.
 */
function addPictureFromGallery() {
    if (typeof canvas === 'undefined') {
        console.warn('Picture Gallery: Design canvas not ready.');
        return;
    }
    // Demo: Add a placeholder image. In a full implementation, this would
    // open a modal to pick from the gallery, then add the selected image.
    var imgUrl = 'https://via.placeholder.com/200x150?text=Picture+Gallery';
    fabric.Image.fromURL(imgUrl, function(img) {
        if (!img) return;
        img.set({
            left: 50,
            top: 50,
            scaleX: 0.5,
            scaleY: 0.5
        });
        canvas.add(img);
        canvas.setActiveObject(img);
        canvas.requestRenderAll();
    });
}
</script>
