import {post, download} from './http-common';

export default {
    fetch(packageId, fetchId, params)
    {
        return post(`content/fetch/packageId/${packageId}`, {fetchId, params});
    },

    download(fileName, baseName, typeId)
    {
        return download(`content/download/name/${fileName}/type/${typeId}`)
            .then(({data}) => {
                const url = URL.createObjectURL(new Blob([data]));
                const link = document.createElement('a');
                link.href = url;
                link.download = baseName;
                link.click();

                link.remove();
                URL.revokeObjectURL(url);
            });
    }
}