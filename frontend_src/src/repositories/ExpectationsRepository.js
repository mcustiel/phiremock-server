import * as request from "superagent";

const EXPECTATIONS_ENDPOINT = 'http://localhost:8080/__phiremock/expectations';

export default class ExpectationsRepository {
    
    async getExpectations() {
        try {
            const result = await request.get(EXPECTATIONS_ENDPOINT)
                .set('Content-Type', 'application/json')
                .set('Accept', 'application/json');
            return JSON.parse(result.text);
        } catch (err) {
            console.log(err);
        }
    }

}