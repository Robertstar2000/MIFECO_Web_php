const { Strategy: JwtStrategy, ExtractJwt } = require('passport-jwt');
const { Strategy: LocalStrategy } = require('passport-local');
const config = require('./config');
const { User } = require('../models');

// JWT options configuration
const jwtOptions = {
  secretOrKey: config.jwt.secret,
  jwtFromRequest: ExtractJwt.fromAuthHeaderAsBearerToken()
};

/**
 * JWT authentication strategy
 * Verifies JWT token and attaches user to request object
 */
const jwtStrategy = new JwtStrategy(jwtOptions, async (payload, done) => {
  try {
    const user = await User.findById(payload.sub);
    
    if (!user) {
      return done(null, false);
    }
    
    // Check if token is issued before password change
    if (payload.iat < user.passwordChangedAt) {
      return done(null, false);
    }
    
    done(null, user);
  } catch (error) {
    done(error, false);
  }
});

/**
 * Local authentication strategy
 * Authenticates user based on email and password
 */
const localStrategy = new LocalStrategy(
  {
    usernameField: 'email',
    passwordField: 'password'
  },
  async (email, password, done) => {
    try {
      const user = await User.findOne({ email }).select('+password');
      
      if (!user) {
        return done(null, false, { message: 'Incorrect email or password' });
      }
      
      const isPasswordMatch = await user.isPasswordMatch(password);
      
      if (!isPasswordMatch) {
        return done(null, false, { message: 'Incorrect email or password' });
      }
      
      done(null, user);
    } catch (error) {
      done(error);
    }
  }
);

module.exports = {
  jwtStrategy,
  localStrategy
};