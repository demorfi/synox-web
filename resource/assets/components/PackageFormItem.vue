<template>
  <b-form>
    <BAlert
        :model-value="true"
        variant="warning"
        class="fs-8">
      <h6 class="alert-heading">Attention!</h6>
      <p class="mb-0">Upload package from trusted sources and only at your own risk!</p>
    </BAlert>
    <b-form-file
        v-model="file"
        :state="state"
        accept="application/x-php"/>
  </b-form>
</template>

<script>
import {mapActions} from 'vuex';

export default {
  data: () => ({
    file : null,
    state: null
  }),

  watch: {
    file()
    {
      this.state = null;
    }
  },

  methods: {
    ...mapActions('packages', ['uploadPackage']),
    reset()
    {
      this.file = this.state = null;
    },

    upload()
    {
      const formData = new FormData();
      formData.append('package', this.file);
      return this.uploadPackage(formData).catch((message) => {
        this.state = false;
        throw new Error(message);
      });
    }
  }
}
</script>