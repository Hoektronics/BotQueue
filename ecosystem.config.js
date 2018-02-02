module.exports = {
  /**
   * Application configuration section
   * http://pm2.keymetrics.io/docs/usage/application-declaration/
   */
  apps : [
    {
      name      : 'WebSocket',
      script    : 'js/socket/ws.js'
    }
  ]
};
