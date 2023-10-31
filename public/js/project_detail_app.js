"use strict";
if (typeof jQuery === "undefined") {
    throw new Error('JQuery framework not found.');
}

class ProjectDetailApp {
    constructor(moduleUuid, projectIds) {

        if (typeof moduleUuid !== 'string') {
            throw new Error(`Param "moduleUuid" must be of type "string", "${typeof moduleUuid}" given.`);
        }

        this.#moduleUuid = moduleUuid;

        if (!Array.isArray(projectIds)) {
            throw new Error(`Param "projectIds" must be of type "array", "${typeof projectIds}" given.`);
        }

        this.#projectIds = projectIds;

        this.currentIndex = 0;
    }

    #currentIndex = 0;
    #moduleUuid = '';
    #projectIds = [];
    #cache = [];

    getModuleUuid() {
        return this.#moduleUuid;
    }

    getProjectIds() {
        return this.#projectIds;
    }

    getCurrentIndex() {
        return this.#currentIndex;
    }

    setCurrentIndex = (intIndex) => {

        if (!Number.isInteger(intIndex)) {
            throw new Error(`Param "intIndex" must be of type "integer", "${typeof intIndex}" given.`);
        }

        if (intIndex < 0) {
            throw new Error(`Param "intIndex" must be 0 or more ${intIndex} given.`);
        }

        if (intIndex >= this.#projectIds.length) {
            throw new Error(`Param "intIndex" cannot be larger then ${this.getProjectIds().length}, ${intIndex} given.`);
        }
        this.#currentIndex = intIndex;
    }

    /**
     * Load project data from server
     * @param projectId
     * @returns {Promise<string>}
     */
    fetchProjectData = async (projectId) => {
        const url = document.location.href + '?project_id=' + projectId + '&module_uuid=' + this.#moduleUuid;
        document.body.classList.add(
            //'is-loading-project',
        );
        if (this.#cache[url]) {
            return await new Promise(resolve => {
                window.setTimeout(() => {
                        document.body.classList.remove('is-loading-project');
                        resolve(this.#cache[url]);
                    },
                    10)
            });
        }

        const response = await fetch(url, {
            method: "GET",
            headers: {
                'x-requested-with': 'XMLHttpRequest',
            }
        });

        const data = await response.json();

        if (data['success'] !== 'true') {
            document.body.classList.remove('is-loading-project');
            throw new Error(`Fetch request failed. ${data['success']}`);
        }

        this.#cache[url] = data['data'];

        return await new Promise(resolve => {
            window.setTimeout(() => {
                    document.body.classList.remove('is-loading-project');
                    resolve(this.#cache[url]);
                },
                10)
        });
    }

    initOwlCarousel = (owlId, carouselOptions = {}) => {
        const owl = jQuery('#' + owlId);

        owl.css('opacity', 0);

        // The initialized event has to be called before owl.owlCarousel()
        owl.on('initialized.owl.carousel', function (eOwl) {
            //jQuery(eOwl.target).fadeOut();
            window.setTimeout(() => {
                jQuery(eOwl.target).css('opacity', 1);
            }, 1000); // wait until images are loaded
        })

        const opt = {
            lazyLoad: false,
            loop: true,
            nav: true,
            center: true,
            margin: 4,
            items: 3,
        }

        carouselOptions = {
            ...opt,
            ...carouselOptions,
        };

        owl.owlCarousel(carouselOptions);
    }

}
