<?php

include_once('autoload.php');

/**
 * On every index.php page load the singleton instance of the Installer
 * class is used to check whether a specific callback exists, but only
 * if the ?callback=XY parameter is passed in the url.
 * 
 * A callback in the context of this project is a set of tasks we have
 * to do on server side, once the user submits a step on the installer.
 * In the configuration.json any step may have a server callback, but
 * callbacks are most useful in combination with the basic_form template.
 * For the basic_form template a nice JSON of the user entered data is handed
 * over to the callback class which you can validate. In other templates
 * only the exact contents of the window is being handed over to the
 * callback. Obviously in the callback you want to do stuff on the
 * server, or with the database etc. to set it up properly according to
 * user input.
 */
final Class Installer {
    /** @var Installer */
    private static $instance;
    /** @var object */
    public $config;
    /** @var string */
    private $clientInitJS;
    /** @var ReflectionClass */
    private $callbackClass;

    /**
     * Loads the configuration file, and sets #clientInitJS, which
     * we can just simply echo out with the #initClient() method at
     * the appropriate place in index.php later.
     */
    private function __construct() {
        $configPath = '../configuration.json'; // Relative to index.php
        if (is_file($configPath)) {
            $configFile = file_get_contents($configPath);
            $configObject = json_decode($configFile);
            if ($configObject === null) {
                $this->clientInitJS = 'APP.show_alert("Error", "Setup cannot proceed, because the configuration is malformed!", false);';
            } else {
                $this->config = $configObject;
                $this->clientInitJS = "APP.config = {$configFile};";
            }
        } else {
            $this->clientInitJS = 'APP.show_alert("Error", "Setup cannot proceed, because the configuration file cannot be found!", false);';
        }
    }

    /**
     * Used to initialize the client side of things. Normally this
     * hands over the configuration, but if the config file could
     * not be loaded it pops up an alert modal.
     *
     * @return void
     */
    public function initClient(): void {
        echo $this->clientInitJS;
    }

    /**
     * The very last thing to load in index.php, responsible for
     * loading a custom theme if one is specified in the config.
     *
     * @return void
     */
    public function loadTheme(): void {
        if (is_object($this->config) &&
        property_exists($this->config, 'options') &&
        property_exists($this->config->options, 'theme') &&
        !empty($this->config->options->theme) &&
        $this->config->options->theme !== 'default' &&
        is_file("resources/theme/{$this->config->options->theme}.css")) {
            echo '<link rel="stylesheet" href="resources/theme/' . $this->config->options->theme . '.css?' . uniqid() . '">';
        } else {
            echo '<script>console.log("No custom theme specified, or failed to load custom theme. Proceeding with the default one.")</script>';
        }
    }

    /**
     * Checks whether callback with the specified name exists.
     * It returns true if it does, and saves the reflection class
     * of the callback handler.
     *
     * @param string $handler
     * @return boolean
     */
    public function callbackExists($handler): bool {
        try {
            $this->callbackClass = new ReflectionClass("\\Installer\\Callback\\{$handler}");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Runs the previously saved reflection class that we
     * determined to be the appropriate callback handler.
     * 
     * Results in a json body and a response code to be set,
     * and termination of php.
     *
     * @param array $userData   Depending on the template used by the installer step
     *                          that invoked the callback, this might be form field
     *                          ids and their values or just simply the html contents
     *                          of the main window. These are user provided parameters
     *                          for the callback.
     * @return void
     */
    public function runCallback($userData): void {
        $callback = $this->callbackClass->newInstance();
        $result = $callback->process($userData);

        echo json_encode((object) [
            'valid' => $result,
            'message' => is_string($callback->message) ? $callback->message : ''
        ]);
        http_response_code($result ? 200 : 400);
        exit(0);
    }

    /**
     * Returns the one and only instance of this class.
     *
     * @return Installer
     */
    public static function getInstance(): Installer {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }
}