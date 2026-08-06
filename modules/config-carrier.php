<script id="theme-config" type="application/json">
{
  "siteUrl": "<?php echo rtrim($this->options->siteUrl, '/'); ?>",
  "base": {
    "themeColor": {
      "hue": <?php echo (int)$this->options->themeColorHue; ?>,
      "fixed": <?php echo $this->options->themeColorFixed ? 'true' : 'false'; ?>
    },
    "banner": {
      "enable": <?php echo $this->options->bannerEnable ? 'true' : 'false'; ?>,
      "src": "<?php echo $this->options->bannerSrc; ?>",
      "position": "<?php echo $this->options->bannerPosition; ?>"
    }
  },
  "style": {
    "color_scheme": "<?php echo $this->options->colorScheme; ?>",
    "enable_change_color_scheme": <?php echo $this->options->enableChangeColorScheme ? 'true' : 'false'; ?>
  }
}
</script>
