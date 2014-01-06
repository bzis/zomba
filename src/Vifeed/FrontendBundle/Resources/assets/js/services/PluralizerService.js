/**
 * Created by vadim on 12/18/13.
 */

'use strict';

advertiserApp.service('PluralizerService', function () {
    this.pluralize = function (number, one, many, other) {
        if (number % 10 == 1 && number % 100 != 11) {
            return one;
        }

        if (number % 10 >= 2 && number % 10 <= 4 && (number % 100 < 10 || number % 100 >= 20)) {
            return many;
        }

        return other;
    }
});
