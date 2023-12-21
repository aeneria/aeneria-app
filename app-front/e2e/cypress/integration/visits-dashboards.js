let utils = require('../utils');

describe('Visit dashboards', () => {
    it('Visit homepage"', () => {

        utils.loadServer();

        utils.login('user-test', 'password');

        cy.wait([
            '@data-sum-elec',
            '@data-repartition-year-elec',
            '@data-sum-dju',
            '@data-repartition-year-temp'
        ]);

        cy.get('#load-more').click();
        cy.wait([
            '@data-sum-elec',
            '@data-repartition-year-elec',
            '@data-sum-dju',
            '@data-repartition-year-temp'
        ]);
    })

    it('Visit electricity dashboard"', () => {

        utils.loadServer();

        utils.login('user-test', 'password');

        cy.visit('/electricity');

        cy.wait([
            '@data-sum-elec',
            '@data-repartition-year-elec',
            '@data-evolution-elec',
            '@data-repartition-week-elec',
            '@data-sum-group-elec',
        ]);
    })

    it('Visit meteo dashboard"', () => {

        utils.loadServer();

        utils.login('user-test', 'password');
        cy.visit('/meteo');

        cy.wait([
            '@data-sum-dju',
            '@data-repartition-year-temp',
            '@data-evolution-dju',
        ]);
        cy.wait([
            '@data-inf-neb',
            '@data-repartition-year-neb',
            '@data-evolution-neb',
        ]);
        cy.wait([
            '@data-inf-rain',
            '@data-repartition-year-rain',
            '@data-evolution-rain',
        ]);
        cy.wait([
            '@data-inf-hum',
            '@data-repartition-year-hum',
            '@data-evolution-hum',
        ]);
    })

    it('Visit energy_x_meteo dashboard"', () => {

        utils.loadServer();

        utils.login('user-test', 'password');
        cy.visit('/energy_x_meteo');

        cy.wait([
            '@data-xy',
            '@data-evolution-elec',
        ]);
    })
})