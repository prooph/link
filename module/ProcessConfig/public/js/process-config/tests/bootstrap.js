ProcessManager.setupForTesting();
ProcessManager.injectTestHelpers();

ProcessManager.ApplicationAdapter = DS.FixtureAdapter;

//Fixtures are provided by the backend, but FixtureAdapter requires the FIXTURES set up
ProcessManager.Process.FIXTURES = [];

