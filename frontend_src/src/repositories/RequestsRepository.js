import * as request from "superagent";

const REQUESTS_ENDPOINT = 'http://localhost:8080/__phiremock/executions';

export default class RequestsRepository {
    
    async getRequests() {
        try {
            const result = await request.put(REQUESTS_ENDPOINT)
                .set('Content-Type', 'application/json')
                .set('Accept', 'application/json');
            return JSON.parse(result.text);
        } catch (err) {
            console.log(err);
        }
    }

}