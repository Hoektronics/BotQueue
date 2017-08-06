const dotenv = require('dotenv');

const config_load_result = dotenv.config();

if (config_load_result.error) {
    throw config_load_result.error;
}

const config = config_load_result.parsed;

module.exports = config;