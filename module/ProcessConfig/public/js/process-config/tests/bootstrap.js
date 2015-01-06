App.setupForTesting();
App.injectTestHelpers();

App.ApplicationAdapter = DS.FixtureAdapter;

//Fixtures are provided by the backend, but FixtureAdapter requires the FIXTURES set up
App.Process.FIXTURES = [];

