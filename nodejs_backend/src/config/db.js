const mongoose = require('mongoose');
const config = require('./config');
const logger = require('./logger');

/**
 * Connect to MongoDB
 */
const connectDB = async () => {
  try {
    const connection = await mongoose.connect(config.mongoose.url, config.mongoose.options);
    logger.info(`MongoDB connected: ${connection.connection.host}`);
    return connection;
  } catch (error) {
    logger.error(`Error connecting to MongoDB: ${error.message}`);
    process.exit(1);
  }
};

/**
 * Close the MongoDB connection
 */
const closeDB = async () => {
  try {
    await mongoose.connection.close();
    logger.info('MongoDB connection closed');
  } catch (error) {
    logger.error(`Error closing MongoDB connection: ${error.message}`);
    process.exit(1);
  }
};

module.exports = {
  connectDB,
  closeDB
};