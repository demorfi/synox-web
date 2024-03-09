<template>
  <component :is="component"/>
</template>

<script>
import IconBiKeyFill from '~icons/bi/key-fill';
import IconBiCaretUpFill from '~icons/bi/caret-up-fill';
import IconBiCaretDownFill from '~icons/bi/caret-down-fill';
import IconBiGithub from '~icons/bi/github';
import IconBiCodeSlash from '~icons/bi/code-slash';
import IconBiHeartFill from '~icons/bi/heart-fill';
import IconBiJournal from '~icons/bi/journal';
import IconBiTrash from '~icons/bi/trash';
import IconBiBoxSeam from '~icons/bi/box-seam';
import IconBiSearch from '~icons/bi/search';
import IconBiGear from '~icons/bi/gear';
import IconBiCollection from '~icons/bi/collection';
import IconBiPencilFill from '~icons/bi/pencil-fill';
import IconBiTrashFill from '~icons/bi/trash-fill';
import IconBiPlusSquareFill from '~icons/bi/plus-square-fill';

export default {
  props: {
    name: String
  },

  setup(props)
  {
    const component = {
      'key-fill'        : IconBiKeyFill,
      'caret-up-fill'   : IconBiCaretUpFill,
      'caret-down-fill' : IconBiCaretDownFill,
      'github'          : IconBiGithub,
      'code-slash'      : IconBiCodeSlash,
      'heart-fill'      : IconBiHeartFill,
      'journal'         : IconBiJournal,
      'trash'           : IconBiTrash,
      'box-seam'        : IconBiBoxSeam,
      'search'          : IconBiSearch,
      'gear'            : IconBiGear,
      'collection'      : IconBiCollection,
      'pencil-fill'     : IconBiPencilFill,
      'trash-fill'      : IconBiTrashFill,
      'plus-square-fill': IconBiPlusSquareFill
    }[props.name];
    return {component};
  }
}
</script>