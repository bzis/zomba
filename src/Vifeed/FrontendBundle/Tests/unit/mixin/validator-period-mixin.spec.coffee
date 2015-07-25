describe 'ValidatorPeriodMixin', ->
  beforeEach( -> module 'mixin.validatorPeriod' )

  describe 'Mixin', ->
    validator = {}
    moment = {}
    expect = chai.expect

    beforeEach(inject ($window, ValidatorPeriodMixin) ->
      validator = new ValidatorPeriodMixin()
      moment = $window.moment
    )

    it 'should return false', ->
      expect(validator.isValid({})).to.be.false
      expect(validator.isValid({ startDate: null })).to.be.false
      expect(validator.isValid({ startDate: null, endDate: '' })).to.be.false

    it 'should return true', ->
      period =
        startDate: moment(),
        endDate: moment()
      expect(validator.isValid(period)).to.be.true
