export default class ExpectationListController {

    constructor(expectationsRepository) {
        this.expectationsRepository = expectationsRepository;
    }

    getExpectations() {
        try {
            return this.expectationsRepository.getExpectations();
        } catch (error) {
            console.log(error);
        }
    }
}