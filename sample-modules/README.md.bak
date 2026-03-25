# Sample Modules

This folder contains sample modules for testing the platform module system.

## Picture Module

To install the Picture Gallery module:

1. Zip the `picture-module` folder (the zip must contain `module.json` at the root or in a single top-level directory)
2. Go to Admin → Settings → Modules
3. Upload the zip file and click "Install Module"

The module will:
- Add a "Picture Gallery" element to the design editor Elements panel
- Add a "Picture Gallery" link to the admin menu (for admin users)
- Provide a placeholder demo when clicking the element (adds a sample image to the canvas)

## Photo Colors Module

To install the Photo Colors Editor module:

1. Use the pre-built `photo-colors-module.zip` or zip the `photo-colors-module` folder
2. Go to Admin → Settings → Modules
3. Upload the zip file and click "Install Module"

The module will:
- Add an "Image Color" tab to the right properties panel when an image is selected in the multi-page design editor
- Provide color adjustment controls: Black & White, Brightness, Contrast, Saturation, Hue, Sepia
- Apply Fabric.js filters in real time to the selected image

## PayHere Module

To install the PayHere payment gateway module:

1. Add `"Modules\\PayHereModule\\": "modules/payhere-module/"` to your `composer.json` autoload.psr-4 (if not already present)
2. Run `composer dump-autoload`
3. Use the pre-built `payhere-module.zip` or zip the `payhere-module` folder
4. Go to Admin → Modules
5. Upload the zip file and click "Install Module"
6. Go to Admin → Settings → Payment to configure PayHere (Merchant ID, Merchant Secret, Sandbox mode)

The module will:
- Add PayHere as a payment option at checkout
- Redirect customers to PayHere for card/mobile wallet payments
- Handle payment confirmation via server callback (notify_url)
- Support sandbox (test) and live modes
