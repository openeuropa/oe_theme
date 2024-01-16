module.exports = {
  name: 'preset-default',
  plugins: [
    {
      name: 'removeViewBox',
      active: false
    },
    { name: 'collapseGroups' },
    { name: 'removeDimensions' },
    {
      name: 'removeAttrs',
      params: {
        attrs: '(fill|stroke|fill-rule)'
      }
    },
    {
      name: 'convertPathData',
      params: {
        noSpaceAfterFlags: false
      }
    },
    { name: 'removeTitle' },
    { name: 'removeComments' },
    { name: 'removeMetadata' }
  ]
};
