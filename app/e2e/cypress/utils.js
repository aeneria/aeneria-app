
/**
 * Connect to front.
 *
 * @param {string} username
 * @param {string} password
 */
const login = function (username, password) {
    cy.getCookie('cypress_current_user').then((cookie) => {
        if (cookie === null || cookie.value !== muna) {
            cy.visit('/')
            cy.get('#inputUsername').clear().type('user-test');
            cy.get('#inputPassword').clear().type('password');
            cy.contains('Connexion').click();
        }
    });
};
module.exports.login = login;

const loadServer = function() {
    cy.server()

    // Data API calls aliases
    ///////////////////////////////////////////////////////

    // Electricity
    cy.route('data/*/sum/conso_elec/**').as('data-sum-elec')
    cy.route('data/*/repartition/conso_elec/year_v/**').as('data-repartition-year-elec')
    cy.route('data/*/repartition/conso_elec/week/**').as('data-repartition-week-elec')
    cy.route('data/*/evolution/conso_elec/**').as('data-evolution-elec')
    cy.route('data/*/sum-group/conso_elec/**').as('data-sum-group-elec')

    // Temperature & DJU
    cy.route('data/*/sum/dju/**').as('data-sum-dju')
    cy.route('data/*/repartition/temperature/year_v/**').as('data-repartition-year-temp')
    cy.route('data/*/evolution/dju/**').as('data-evolution-dju')

    // Nebulosity
    cy.route('data/*/inf/nebulosity/**').as('data-inf-neb')
    cy.route('data/*/repartition/nebulosity/year_v/**').as('data-repartition-year-neb')
    cy.route('data/*/evolution/nebulosity/**').as('data-evolution-neb')

    // Rain
    cy.route('data/*/inf/rain/**').as('data-inf-rain')
    cy.route('data/*/repartition/rain/year_v/**').as('data-repartition-year-rain')
    cy.route('data/*/evolution/rain/**').as('data-evolution-rain')

    // Humidity
    cy.route('data/*/inf/humidity/**').as('data-inf-hum')
    cy.route('data/*/repartition/humidity/year_v/**').as('data-repartition-year-hum')
    cy.route('data/*/evolution/humidity/**').as('data-evolution-hum')

    // Comparision
    cy.route('data/*/xy/**').as('data-xy')
};
module.exports.loadServer = loadServer;